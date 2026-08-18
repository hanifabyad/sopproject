<?php

namespace App\Services;

use App\Models\User;
use Smalot\PdfParser\Parser;
use Exception;

class PdfSignaturePositionResolver
{
    /**
     * Resolves visual signature coordinates (X, Y) for a given signer on an LP PDF.
     *
     * @param string $fileLpPath Absolute file path to original file_lp
     * @param User|string $userOrName User instance or string name
     * @return array ['page' => 1, 'x' => float, 'y' => float, 'anchor' => string]
     * @throws Exception If file invalid, name not configured, not found, ambiguous, or out-of-bounds
     */
    public function resolvePosition(string $fileLpPath, $userOrName): array
    {
        if (!file_exists($fileLpPath)) {
            throw new Exception("File LP tidak ditemukan di path: {$fileLpPath}");
        }

        $targetName = $this->extractSignerName($userOrName);
        if (empty($targetName)) {
            throw new Exception("Nama lengkap (full_name) signer belum dikonfigurasi pada sistem database.");
        }

        $parser = new Parser();
        try {
            $parsedPdf = $parser->parseFile($fileLpPath);
            $pages = $parsedPdf->getPages();
            if (empty($pages)) {
                throw new Exception("PDF tidak memiliki halaman.");
            }
            $page = $pages[0];
        } catch (\Throwable $e) {
            throw new Exception("Gagal membaca dokumen PDF: " . $e->getMessage());
        }

        $details = $page->getDetails();
        $rotate = (int)($details['Rotate'] ?? ($details['rotate'] ?? 0));
        if ($rotate !== 0) {
            throw new Exception("Resolusi posisi otomatis belum mendukung halaman PDF dengan rotasi ({$rotate}°).");
        }

        $mediaBox = $details['MediaBox'] ?? ($details['mediabox'] ?? [0, 0, 612, 792]);
        $pageWPt = (float)($mediaBox[2] ?? 612.0);
        $pageHPt = (float)($mediaBox[3] ?? 792.0);

        $pageWidthMm = $pageWPt * 25.4 / 72.0;
        $pageHeightMm = $pageHPt * 25.4 / 72.0;

        $dataTm = $page->getDataTm();
        if (empty($dataTm)) {
            throw new Exception("PDF Lembar Pengesahan tidak memuat stream teks yang dapat dibaca.");
        }

        // 1. Group text items into visual Y-rows (tolerance 1.5 pt)
        $yRows = [];
        foreach ($dataTm as $item) {
            $rawText = trim($item[1] ?? '');
            if (empty($rawText)) {
                continue;
            }
            $rawX = (float)($item[0][4] ?? 0);
            $rawY = (float)($item[0][5] ?? 0);

            $matchedRowKey = null;
            foreach (array_keys($yRows) as $rKey) {
                if (abs($rawY - (float)$rKey) <= 1.5) {
                    $matchedRowKey = $rKey;
                    break;
                }
            }

            if ($matchedRowKey === null) {
                $matchedRowKey = (string)$rawY;
                $yRows[$matchedRowKey] = [];
            }

            $yRows[$matchedRowKey][] = [
                'x'    => $rawX,
                'y'    => $rawY,
                'text' => $rawText
            ];
        }

        // 2. Build full line objects for each Y-row
        $lineObjects = [];
        foreach ($yRows as $yKey => $items) {
            usort($items, fn($a, $b) => $a['x'] <=> $b['x']);
            $fullLineText = implode(' ', array_map(fn($it) => $it['text'], $items));
            $rawY = (float)$yKey;

            // Handle CTM Matrix (Negative rawY vs standard bottom-origin rawY)
            if ($rawY < 0) {
                $markerMmY = abs($rawY) * 25.4 / 72.0;
            } else {
                $markerMmY = ($pageHPt - $rawY) * 25.4 / 72.0;
            }

            $lineObjects[] = [
                'raw_y'     => $rawY,
                'line_text' => $fullLineText,
                'mm_y'      => $markerMmY
            ];
        }

        $normalizedTarget = $this->normalizeText($targetName);
        $matchedRows = [];

        // 3. Strict 2-tier deterministic matching:
        // Tier 1: Exact match
        // Tier 2: Target full name contained in visual line text
        foreach ($lineObjects as $row) {
            $normalizedLine = $this->normalizeText($row['line_text']);

            if ($normalizedLine === $normalizedTarget || str_contains($normalizedLine, $normalizedTarget)) {
                $matchedRows[] = $row;
            }
        }

        if (empty($matchedRows)) {
            throw new Exception("Signer dengan nama '{$targetName}' tidak ditemukan pada Lembar Pengesahan.");
        }

        // Deduplicate identical Y coordinates
        $uniqueYMatches = [];
        foreach ($matchedRows as $match) {
            $yKey = sprintf("%.1f", $match['mm_y']);
            if (!isset($uniqueYMatches[$yKey])) {
                $uniqueYMatches[$yKey] = $match;
            }
        }

        if (count($uniqueYMatches) > 1) {
            throw new Exception("Ambigu: Nama signer '{$targetName}' ditemukan lebih dari satu kali pada posisi baris berbeda.");
        }

        $bestMatch = reset($uniqueYMatches);
        $markerMmY = $bestMatch['mm_y'];

        // Compute Y coordinate top-left for FPDI stamp (stamp height 5.2mm, centered on row)
        $stampY = round($markerMmY - 4.10, 2);

        // Compute X coordinate centered inside signature column (ratio 68.24% of page width)
        $sigColumnCenterX = $pageWidthMm * 0.6824;
        $stampX = round($sigColumnCenterX - 12.50, 2);

        // Safety Boundary Validation
        $stampWidthMm = 25.0;
        $stampHeightMm = 5.2;

        if ($stampX < 0 || ($stampX + $stampWidthMm) > $pageWidthMm || $stampY < 0 || ($stampY + $stampHeightMm) > $pageHeightMm) {
            throw new Exception("Koordinat tanda tangan yang dihasilkan (X: {$stampX}mm, Y: {$stampY}mm) berada di luar batas fisik halaman PDF.");
        }

        return [
            'page'   => 1,
            'x'      => $stampX,
            'y'      => $stampY,
            'anchor' => $bestMatch['line_text']
        ];
    }

    private function extractSignerName($userOrName): string
    {
        if ($userOrName instanceof User) {
            return trim($userOrName->full_name ?? '');
        }
        if (is_array($userOrName)) {
            return trim($userOrName['full_name'] ?? '');
        }
        return trim((string)$userOrName);
    }

    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
