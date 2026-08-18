<?php

namespace App\Services;

use App\Models\User;
use Smalot\PdfParser\Parser;
use Exception;

class LpSectionSignerParser
{
    /**
     * Normalize text for string comparisons: lowercase, single space.
     */
    private function normalizeText(string $text): string
    {
        $text = mb_strtolower($text, 'UTF-8');
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    /**
     * Parse text-based LP PDF and auto-detect Creator, Reviewers, and Final Signers.
     *
     * @param string $pdfPath Absolute path to original file_lp
     * @return array ['creator' => [User], 'reviewers' => [User, User, ...], 'final' => [User]]
     * @throws Exception If PDF non-text, sections missing, signer unknown, or ambiguous match.
     */
    public function parseAndMatchSigners(string $pdfPath): array
    {
        if (!file_exists($pdfPath)) {
            throw new Exception("File Lembar Pengesahan tidak ditemukan di path: {$pdfPath}");
        }

        $parser = new Parser();
        try {
            $pdf = $parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            if (empty($pages)) {
                throw new Exception("PDF tidak memiliki halaman.");
            }
            $page = $pages[0];
            $dataTm = $page->getDataTm();
        } catch (\Throwable $e) {
            throw new Exception("Lembar Pengesahan tidak memiliki teks PDF yang dapat dibaca oleh sistem. Gunakan file PDF text-based atau PDF yang teksnya dapat diseleksi.");
        }

        if (empty($dataTm)) {
            throw new Exception("Lembar Pengesahan tidak memiliki teks PDF yang dapat dibaca oleh sistem. Gunakan file PDF text-based atau PDF yang teksnya dapat diseleksi.");
        }

        // Group into Y-rows with 2.0pt tolerance
        $items = [];
        foreach ($dataTm as $tmItem) {
            $txt = trim($tmItem[1] ?? '');
            if ($txt === '') continue;
            $matrix = $tmItem[0] ?? [];
            $y = (float)($matrix[5] ?? 0.0);
            $x = (float)($matrix[4] ?? 0.0);
            $items[] = ['text' => $txt, 'x' => $x, 'y' => $y];
        }

        usort($items, fn($a, $b) => $b['y'] <=> $a['y']);

        $rows = [];
        foreach ($items as $item) {
            $foundRow = false;
            foreach ($rows as $rIdx => $row) {
                if (abs($row['y'] - $item['y']) <= 2.0) {
                    $rows[$rIdx]['items'][] = $item;
                    $foundRow = true;
                    break;
                }
            }
            if (!$foundRow) {
                $rows[] = ['y' => $item['y'], 'items' => [$item]];
            }
        }

        $lines = [];
        foreach ($rows as $row) {
            usort($row['items'], fn($a, $b) => $a['x'] <=> $b['x']);
            $lineText = implode(' ', array_map(fn($it) => $it['text'], $row['items']));
            $lines[] = ['y' => $row['y'], 'text' => $lineText];
        }

        $currentSection = null;
        $sectionRows = [
            'creator'  => [],
            'reviewer' => [],
            'final'    => []
        ];

        foreach ($lines as $line) {
            $norm = $this->normalizeText($line['text']);

            if (str_contains($norm, 'pembuat dokumen') || str_contains($norm, 'dibuat oleh')) {
                $currentSection = 'creator';
                continue;
            }
            if (str_contains($norm, 'diperiksa') || str_contains($norm, 'ditinjau') || str_contains($norm, 'diketahui oleh')) {
                $currentSection = 'reviewer';
                continue;
            }
            if (str_contains($norm, 'disahkan oleh') || str_contains($norm, 'disetujui oleh')) {
                $currentSection = 'final';
                continue;
            }

            if (!$currentSection) continue;

            // Skip table headers / footer notes
            if (str_contains($norm, 'jabatan') || str_contains($norm, 'kode') || str_contains($norm, 'nama') ||
                str_contains($norm, 'tanda tangan') || str_contains($norm, 'tanggal') || str_contains($norm, 'keterangan:') ||
                str_contains($norm, 'lembar tinjauan')) {
                continue;
            }

            // Remove signature placeholders like [sig01]
            $cleanText = preg_replace('/\[sig\d+\]/i', '', $line['text']);
            $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

            if (!empty($cleanText)) {
                if (empty($sectionRows[$currentSection]) || end($sectionRows[$currentSection]) !== $cleanText) {
                    $sectionRows[$currentSection][] = $cleanText;
                }
            }
        }

        if (empty($sectionRows['creator'])) {
            throw new Exception("Section 'Pembuat Dokumen' tidak ditemukan pada Lembar Pengesahan.");
        }
        if (empty($sectionRows['reviewer'])) {
            throw new Exception("Section 'Diperiksa dan Diketahui oleh' tidak ditemukan pada Lembar Pengesahan.");
        }
        if (empty($sectionRows['final'])) {
            throw new Exception("Section 'Disahkan oleh' tidak ditemukan pada Lembar Pengesahan.");
        }

        $allUsers = User::whereNotNull('full_name')->where('full_name', '!=', '')->get();
        $results = [
            'creator'   => [],
            'reviewers' => [],
            'final'     => []
        ];

        foreach (['creator', 'reviewer', 'final'] as $sec) {
            foreach ($sectionRows[$sec] as $rowText) {
                $normRow = $this->normalizeText($rowText);
                $matchedUsers = [];

                foreach ($allUsers as $user) {
                    $normName = $this->normalizeText($user->full_name);
                    if (empty($normName)) continue;

                    if ($normRow === $normName || str_contains($normRow, $normName)) {
                        $matchedUsers[] = $user;
                    }
                }

                if (empty($matchedUsers)) {
                    $secLabel = ($sec === 'creator') ? 'Pembuat Dokumen' : (($sec === 'reviewer') ? 'Diperiksa dan Diketahui oleh' : 'Disahkan oleh');
                    throw new Exception("Nama '{$rowText}' pada section '{$secLabel}' tidak terdaftar sebagai pengguna sistem.");
                }

                if (count($matchedUsers) > 1) {
                    $names = implode(', ', array_map(fn($u) => "'{$u->full_name}'", $matchedUsers));
                    throw new Exception("Ambigu: Teks pada Lembar Pengesahan ('{$rowText}') cocok dengan lebih dari satu pengguna ({$names}). Silakan periksa data pegawai.");
                }

                $user = $matchedUsers[0];
                $targetKey = ($sec === 'reviewer') ? 'reviewers' : $sec;
                
                // Deduplicate user by ID inside section
                $alreadyAdded = false;
                foreach ($results[$targetKey] as $addedUser) {
                    if ($addedUser->id === $user->id) {
                        $alreadyAdded = true;
                        break;
                    }
                }

                if (!$alreadyAdded) {
                    $results[$targetKey][] = $user;
                }
            }
        }

        if (count($results['creator']) !== 1) {
            throw new Exception("Section 'Pembuat Dokumen' harus berisi tepat 1 penandatangan.");
        }
        if (count($results['final']) !== 1) {
            throw new Exception("Section 'Disahkan oleh' harus berisi tepat 1 penandatangan.");
        }

        return $results;
    }
}
