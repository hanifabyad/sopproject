<?php

namespace App\Services;

use App\Models\User;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class LpGeneratorService
{
    /**
     * Generate a standardized Lembar Pengesahan (LP) PDF page.
     *
     * @param array $data Metadata for the LP:
     *                    - 'title' => string
     *                    - 'doc_number' => string
     *                    - 'doc_revision' => string
     *                    - 'doc_date' => string
     *                    - 'company_header' => string ('sck', 'pkm', 'cpt', 'lbs')
     *                    - 'total_pages' => int
     * @param User $creator The document creator User instance
     * @param array|\Illuminate\Support\Collection $reviewers Collection of reviewer User instances
     * @param User $finalApprover The final approver User instance
     * @return array ['file_path' => string, 'coordinates' => array]
     */
    public function generate(array $data, User $creator, $reviewers, User $finalApprover): array
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $title = $data['title'] ?? 'DOKUMEN SOP';
        $docNumber = $data['doc_number'] ?? '-';
        $revision = $data['doc_revision'] ?? '0';
        $docDate = $data['doc_date'] ?? date('d F Y');
        $companyHeader = $data['company_header'] ?? 'pkm';
        $totalPages = $data['total_pages'] ?? 2;

        // Draw Outer Border
        $pdf->SetDrawColor(30, 41, 59); // Slate-800
        $pdf->SetLineWidth(0.4);
        $pdf->Rect(15, 15, 180, 267);

        // ---------------------------------------------------------
        // 1. HEADER SECTION (Y: 15 to 35)
        // ---------------------------------------------------------
        $pdf->Line(15, 35, 195, 35);
        $pdf->Line(60, 15, 60, 35); // Split column

        // Draw Logo
        $pdf->SetTextColor(30, 41, 59);
        if ($companyHeader === 'sck') {
            // Draw beautiful vector text logo for SCK
            $pdf->SetDrawColor(30, 64, 175); // Blue
            $pdf->SetLineWidth(0.6);
            $pdf->Rect(22.5, 17.5, 30, 15);
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->SetTextColor(30, 64, 175);
            $pdf->SetXY(22.5, 17.5);
            $pdf->Cell(30, 15, 'SCK', 0, 0, 'C');
        } else {
            // PKM, LBS, CPT use PKM logo image
            $logoPath = public_path('img/logopkm.png');
            if (file_exists($logoPath)) {
                $pdf->Image($logoPath, 22.5, 17.5, 30);
            } else {
                // Fallback text logo if image missing
                $pdf->SetDrawColor(16, 185, 129); // Emerald
                $pdf->SetLineWidth(0.6);
                $pdf->Rect(22.5, 17.5, 30, 15);
                $pdf->SetFont('Arial', 'B', 14);
                $pdf->SetTextColor(16, 185, 129);
                $pdf->SetXY(22.5, 17.5);
                $pdf->Cell(30, 15, 'PKM', 0, 0, 'C');
            }
        }

        // Company text
        $companyName = 'PT PUTRA KELANA MAKMUR (PKM) GROUP';
        $companyAddress = 'Jl. Budi Kemuliaan No. 3 Seraya, Batam';
        if ($companyHeader === 'sck') {
            $companyName = 'PT SATRIA CITRA KENCANA';
            $companyAddress = 'Jl. Budi Kemuliaan No. 3 Seraya, Batam';
        } elseif ($companyHeader === 'lbs') {
            $companyName = 'PT LINTAS BINTAN SAMUDERA';
        } elseif ($companyHeader === 'cpt') {
            $companyName = 'PT CAHAYA PERDANA TRANSALAM';
        }

        $pdf->SetXY(60, 19);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(135, 6, $companyName, 0, 1, 'C');
        
        $pdf->SetXY(60, 25);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(100, 116, 139); // Slate-500
        $pdf->Cell(135, 4, $companyAddress, 0, 1, 'C');

        // ---------------------------------------------------------
        // 2. TITLE & METADATA SECTION (Y: 35 to 60)
        // ---------------------------------------------------------
        $pdf->Line(15, 60, 195, 60);
        $pdf->Line(135, 35, 135, 60); // Column split

        // Document Title
        $pdf->SetXY(16, 38);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->MultiCell(118, 5, strtoupper($title), 0, 'C');

        // Sub-table grids
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(203, 213, 225); // Slate-300
        $pdf->Line(135, 41.25, 195, 41.25);
        $pdf->Line(135, 47.5, 195, 47.5);
        $pdf->Line(135, 53.75, 195, 53.75);
        $pdf->Line(155, 35, 155, 60); // Label-value split

        $metadata = [
            ['No Dok', $docNumber],
            ['Revisi', $revision],
            ['Tanggal', $docDate],
            ['Halaman', '2 dari ' . $totalPages],
        ];

        $yMeta = 35.0;
        foreach ($metadata as $idx => $meta) {
            $pdf->SetXY(136, $yMeta + 1.2);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell(18, 4, $meta[0], 0, 0, 'L');

            $pdf->SetXY(156, $yMeta + 1.2);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(30, 41, 59);
            $this->drawCellFit($pdf, 38, 4, $meta[1], 0, 0, 'L');

            $yMeta += 6.25;
        }

        // ---------------------------------------------------------
        // 3. LEMBAR TINJAUAN TITLE (Y: 60 to 70)
        // ---------------------------------------------------------
        $pdf->SetLineWidth(0.4);
        $pdf->SetDrawColor(30, 41, 59);
        $pdf->Line(15, 70, 195, 70);
        $pdf->SetXY(15, 63);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(180, 4, 'LEMBAR TINJAUAN DAN PENGESAHAN DOKUMEN', 0, 1, 'C');

        // ---------------------------------------------------------
        // 4. TABLE HEADER (Y: 70 to 78)
        // ---------------------------------------------------------
        $pdf->Line(15, 78, 195, 78);
        $pdf->Line(80, 70, 80, 78);
        $pdf->Line(125, 70, 125, 78);
        $pdf->Line(165, 70, 165, 78);

        $headers = [
            [15, 65, 'Jabatan'],
            [80, 45, 'Kode Nama'],
            [125, 40, 'Tanda Tangan'],
            [165, 30, 'Tanggal']
        ];

        foreach ($headers as $h) {
            $pdf->SetXY($h[0], 72);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(30, 41, 59);
            $pdf->Cell($h[1], 4, $h[2], 0, 0, 'C');
        }

        // ---------------------------------------------------------
        // 5. TABLE BODY ROWS
        // ---------------------------------------------------------
        $currentY = 78.0;
        $coordinates = [];

        $numReviewers = count($reviewers);
        $rowHeight = 12.0;
        $headerHeight = 6.0;

        if ($numReviewers > 5) {
            // Shrink row height and header height proportionally so 8 reviewers fit perfectly in 1 page.
            $rowHeight = max(8.0, 12.0 - ($numReviewers - 5) * 1.2);
            $headerHeight = max(4.5, 6.0 - ($numReviewers - 5) * 0.4);
        }

        // Helper to draw a row section
        $drawSectionHeader = function ($label) use ($pdf, &$currentY, $headerHeight) {
            $pdf->SetFillColor(241, 245, 249); // Slate-100
            $pdf->Rect(15, $currentY, 180, $headerHeight, 'F');
            $pdf->SetLineWidth(0.2);
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->Line(15, $currentY + $headerHeight, 195, $currentY + $headerHeight);

            $pdf->SetXY(17, $currentY + ($headerHeight - 4.0) / 2.0);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(71, 85, 105); // Slate-600
            $pdf->Cell(176, 4, $label, 0, 1, 'L');
            $currentY += $headerHeight;
        };

        $drawSignerRow = function (User $user) use ($pdf, &$currentY, &$coordinates, $rowHeight) {
            $pdf->SetLineWidth(0.2);
            $pdf->SetDrawColor(203, 213, 225);
            $pdf->Line(15, $currentY + $rowHeight, 195, $currentY + $rowHeight);
            $pdf->Line(80, $currentY, 80, $currentY + $rowHeight);
            $pdf->Line(125, $currentY, 125, $currentY + $rowHeight);
            $pdf->Line(165, $currentY, 165, $currentY + $rowHeight);

            // Column 1: Jabatan / Role
            $pdf->SetTextColor(30, 41, 59);
            $this->drawCellWrapped($pdf, 17, $currentY, 61, $rowHeight, $user->role ?? '-', 7.5, false);

            // Column 2: Kode Nama / Full Name
            $this->drawCellWrapped($pdf, 82, $currentY, 41, $rowHeight, $user->full_name ?? $user->username, 7.5, true);

            // Compute math coordinate for 100% center stamp placement
            // Signature Column starts at X=125, width=40. Center is 145.
            $centeredX = 145.0 - 12.5; // 132.50 mm (center of 40mm column)
            $centeredY = $currentY + ($rowHeight / 2.0) - 2.6; // Vertical center based on rowHeight

            // Adjust coordinates to compensate for ReviewerController's draw offsets (+7.2 X, +0.9 Y)
            $stampX = $centeredX - 7.2; 
            $stampY = $centeredY - 0.9; 

            $coordinates[] = [
                'user_id' => $user->id,
                'page'    => 1, // Will map to page 2 inside merged document
                'x'       => round($stampX, 2),
                'y'       => round($stampY, 2)
            ];

            $currentY += $rowHeight;
        };

        // Section A: Pembuat Dokumen
        $drawSectionHeader('Pembuat Dokumen');
        $drawSignerRow($creator);

        // Section B: Diperiksa dan Diketahui oleh:
        $drawSectionHeader('Diperiksa dan Diketahui oleh:');
        foreach ($reviewers as $rev) {
            $drawSignerRow($rev);
        }

        // Section C: Disahkan oleh:
        $drawSectionHeader('Disahkan oleh:');
        $drawSignerRow($finalApprover);

        // ---------------------------------------------------------
        // 6. FOOTER NOTES (Y: 272)
        // ---------------------------------------------------------
        $pdf->SetXY(15, 272);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(148, 163, 184); // Slate-400
        $pdf->Cell(180, 4, 'Keterangan: NA (Not Applicable), apabila tidak diperlukan pemeriksaan dan persetujuan dari Pejabat terkait', 0, 1, 'L');

        // Output and save PDF to public storage
        $fileName = 'generated_lp_' . time() . '_' . uniqid() . '.pdf';
        $lpPath = 'documents/lps/' . $fileName;
        $absPath = storage_path('app/public/' . $lpPath);

        // Ensure directories exist
        if (!is_dir(dirname($absPath))) {
            mkdir(dirname($absPath), 0755, true);
        }

        $pdf->Output('F', $absPath);

        return [
            'file_path'   => $lpPath,
            'coordinates' => $coordinates
        ];
    }

    private function drawCellWrapped($pdf, $x, $y, $w, $h, $txt, $maxFont = 8.0, $isBold = false)
    {
        $fontStyle = $isBold ? 'B' : '';
        $pdf->SetFont('Arial', $fontStyle, $maxFont);
        
        // Split by words
        $words = explode(' ', $txt);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $word) {
            $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
            if ($pdf->GetStringWidth($testLine) > $w - 2) {
                if ($currentLine !== '') {
                    $lines[] = $currentLine;
                }
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }
        
        // If Y height overflows, shrink size and try again
        $fontSizePt = $maxFont;
        $lineHeightMm = $fontSizePt * 0.35 + 0.3;
        $totalTextHeight = count($lines) * $lineHeightMm;
        
        if ($totalTextHeight > $h - 1.0 && $maxFont > 5.0) {
            $fontSizePt = $maxFont - 1.5;
            $pdf->SetFont('Arial', $fontStyle, $fontSizePt);
            $lines = [];
            $currentLine = '';
            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                if ($pdf->GetStringWidth($testLine) > $w - 2) {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                    }
                    $currentLine = $word;
                } else {
                    $currentLine = $testLine;
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
            $lineHeightMm = $fontSizePt * 0.35 + 0.3;
            $totalTextHeight = count($lines) * $lineHeightMm;
        }
        
        // Vertically center inside row Y height
        $startY = $y + ($h - $totalTextHeight) / 2.0;
        
        foreach ($lines as $i => $line) {
            $pdf->SetXY($x, $startY + $i * $lineHeightMm);
            $pdf->Cell($w, $lineHeightMm, $line, 0, 0, 'L');
        }
    }

    private function drawCellFit($pdf, $w, $h, $txt, $border = 0, $ln = 0, $align = '', $fill = false)
    {
        $defaultFontSize = 8.0; 
        $currentSize = $defaultFontSize;
        
        while ($pdf->GetStringWidth($txt) > $w - 2 && $currentSize > 5.0) {
            $currentSize -= 0.5;
            $pdf->SetFontSize($currentSize);
        }
        $pdf->Cell($w, $h, $txt, $border, $ln, $align, $fill);
        $pdf->SetFontSize($defaultFontSize);
    }
}
