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

        // ---------------------------------------------------------
        // GLOBAL STYLE: Formal black borders throughout
        // ---------------------------------------------------------
        $BLACK = [0, 0, 0];
        $GRAY_LIGHT = [245, 245, 245]; // very light gray for section headers
        $GRAY_TEXT  = [80, 80, 80];    // dark gray for section label text

        // Uniform border constants
        $LINE_OUTER = 0.5;  // outer page border
        $LINE_TABLE = 0.35; // all internal table lines

        // Draw Outer Border — black, slightly thick
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_OUTER);
        $pdf->Rect(15, 15, 180, 267);

        // ---------------------------------------------------------
        // 1. HEADER SECTION (Y: 15 to 42) — height = 27mm
        //    Allows a 24mm logo with 1.5mm margin top & bottom.
        // ---------------------------------------------------------
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->Line(15, 42, 195, 42);  // Bottom of header
        $pdf->Line(60, 15, 60, 42);   // Logo | Company text split

        // --- LOGO (centered inside left cell: X=15..60, Y=15..42) ---
        // Cell dimensions: width=45mm, height=27mm
        // Logo rendered at 24mm × 24mm, centered with 1.5mm margin each side.
        $logoCellX = 15.0;
        $logoCellY = 15.0;
        $logoCellW = 45.0;
        $logoCellH = 27.0;

        $pdf->SetTextColor(30, 41, 59);
        if ($companyHeader === 'sck') {
            // SCK: vector text logo — blue box centered in cell
            $logoW = 24.0; $logoH = 12.0;
            $logoX = $logoCellX + ($logoCellW - $logoW) / 2.0;
            $logoY = $logoCellY + ($logoCellH - $logoH) / 2.0;
            $pdf->SetDrawColor(30, 64, 175);
            $pdf->SetLineWidth(0.5);
            $pdf->Rect($logoX, $logoY, $logoW, $logoH);
            $pdf->SetFont('Arial', 'B', 13);
            $pdf->SetTextColor(30, 64, 175);
            $pdf->SetXY($logoX, $logoY);
            $pdf->Cell($logoW, $logoH, 'SCK', 0, 0, 'C');
            $pdf->SetDrawColor(...$BLACK);
        } else {
            // PKM / LBS / CPT: use PKM logo image, centered
            $logoPath = public_path('img/logopkm.png');
            if (file_exists($logoPath)) {
                // logopkm.png is 200x200px (square). Render at 24mm × 24mm.
                // Cell is 45mm wide × 27mm tall → margin: (45-24)/2=10.5mm H, (27-24)/2=1.5mm V
                $logoW = 24.0;
                $logoH = 24.0;
                $logoX = $logoCellX + ($logoCellW - $logoW) / 2.0; // 26.5mm from left edge
                $logoY = $logoCellY + ($logoCellH - $logoH) / 2.0; // 16.5mm from top edge
                $pdf->Image($logoPath, $logoX, $logoY, $logoW, $logoH);
            } else {
                // Fallback text logo
                $logoW = 24.0; $logoH = 12.0;
                $logoX = $logoCellX + ($logoCellW - $logoW) / 2.0;
                $logoY = $logoCellY + ($logoCellH - $logoH) / 2.0;
                $pdf->SetDrawColor(16, 185, 129);
                $pdf->SetLineWidth(0.5);
                $pdf->Rect($logoX, $logoY, $logoW, $logoH);
                $pdf->SetFont('Arial', 'B', 13);
                $pdf->SetTextColor(16, 185, 129);
                $pdf->SetXY($logoX, $logoY);
                $pdf->Cell($logoW, $logoH, 'PKM', 0, 0, 'C');
                $pdf->SetDrawColor(...$BLACK);
            }
        }

        // --- Company name & address, vertically centered in right cell ---
        $companyName = 'PT PUTRA KELANA MAKMUR (PKM) GROUP';
        $companyAddress = 'Jl. Budi Kemuliaan No. 3 Seraya, Batam';
        if ($companyHeader === 'sck') {
            $companyName = 'PT SATRIA CITRA KENCANA';
        } elseif ($companyHeader === 'lbs') {
            $companyName = 'PT LINTAS BINTAN SAMUDERA';
        } elseif ($companyHeader === 'cpt') {
            $companyName = 'PT CAHAYA PERDANA TRANSALAM';
        }

        // Right cell: X=60..195, Y=15..42
        // Vertically center company name and address in 27mm cell
        $pdf->SetXY(60, 24.0);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(135, 6, $companyName, 0, 1, 'C');

        $pdf->SetXY(60, 31.0);
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(80, 80, 80);
        $pdf->Cell(135, 4, $companyAddress, 0, 1, 'C');

        // ---------------------------------------------------------
        // 2. TITLE & METADATA SECTION (Y: 42 to 67) — height 25mm
        // ---------------------------------------------------------
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->Line(15, 67, 195, 67);
        $pdf->Line(135, 42, 135, 67); // Title | Meta split

        // Document Title — auto-shrink so text stays within Y=42..67 (25mm area)
        // -----------------------------------------------------------------------
        // Area constants
        $titleX      = 16.0;          // left start (1mm inside outer border)
        $titleAreaY  = 42.0;          // top of Title+Meta section
        $titleAreaH  = 25.0;          // total cell height (Y=42..67)
        $titleW      = 116.0;         // usable width (118 - 1mm margin each side)
        $titlePadV   = 2.0;           // vertical padding: 2mm top + 2mm bottom
        $maxContentH = $titleAreaH - ($titlePadV * 2); // 21mm usable
        $titleText   = strtoupper(trim($title));

        // Map: font size → line height (mm) — proportional to font size
        $fontSteps = [
            13 => 6.5,
            12 => 6.0,
            11 => 5.5,
            10 => 5.0,
             9 => 4.5,
        ];

        // Word-wrap helper: splits $text into lines that fit in $width at current font
        $wrapLines = function (string $text, float $width) use ($pdf): array {
            $words   = preg_split('/\s+/', $text);
            $lines   = [];
            $current = '';
            foreach ($words as $word) {
                $test = $current === '' ? $word : $current . ' ' . $word;
                if ($pdf->GetStringWidth($test) <= $width) {
                    $current = $test;
                } else {
                    if ($current !== '') $lines[] = $current;
                    $current = $word;
                }
            }
            if ($current !== '') $lines[] = $current;
            return $lines;
        };

        // Find largest font that fits all lines inside maxContentH
        $chosenSize  = 9;
        $chosenLineH = 4.5;
        $chosenLines = [];

        foreach ($fontSteps as $fs => $lh) {
            $pdf->SetFont('Arial', 'B', $fs);
            $lines  = $wrapLines($titleText, $titleW);
            $totalH = count($lines) * $lh;
            if ($totalH <= $maxContentH) {
                $chosenSize  = $fs;
                $chosenLineH = $lh;
                $chosenLines = $lines;
                break; // largest fitting size found — stop
            }
            // Store lines at this size in case we need the minimum fallback
            if ($fs === 9) {
                $chosenLines = $lines;
            }
        }

        // Safety: if even 9pt overflows, hard-truncate to max safe line count
        $maxLines = (int) floor($maxContentH / $chosenLineH);
        if (count($chosenLines) > $maxLines) {
            // Truncate and append ellipsis to last visible line
            $chosenLines = array_slice($chosenLines, 0, $maxLines);
            $last = end($chosenLines);
            // Trim last line until ellipsis fits
            $pdf->SetFont('Arial', 'B', $chosenSize);
            while ($pdf->GetStringWidth($last . '...') > $titleW && strlen($last) > 0) {
                $last = rtrim(substr($last, 0, -1));
            }
            $chosenLines[count($chosenLines) - 1] = $last . '...';
        }

        // Render: vertically center the text block inside the 25mm area
        $totalTextH = count($chosenLines) * $chosenLineH;
        $startY     = $titleAreaY + ($titleAreaH - $totalTextH) / 2.0;

        $pdf->SetFont('Arial', 'B', $chosenSize);
        $pdf->SetTextColor(30, 41, 59);
        foreach ($chosenLines as $line) {
            $pdf->SetXY($titleX, $startY);
            $pdf->Cell($titleW, $chosenLineH, $line, 0, 0, 'C');
            $startY += $chosenLineH;
        }

        // Metadata sub-grid lines — uniform 0.35mm black
        // 4 rows over 25mm → each 6.25mm; starts at Y=42
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->SetDrawColor(...$BLACK);
        $pdf->Line(135, 48.25, 195, 48.25);
        $pdf->Line(135, 54.5,  195, 54.5);
        $pdf->Line(135, 60.75, 195, 60.75);
        $pdf->Line(155, 42,    155, 67);    // Label | Value split

        $metadata = [
            ['No Dok',  $docNumber],
            ['Revisi',  $revision],
            ['Tanggal', $docDate],
            ['Halaman', '2 dari ' . $totalPages],
        ];

        $yMeta = 42.0; // starts at top of Title+Meta section
        foreach ($metadata as $meta) {
            $pdf->SetXY(136, $yMeta + 1.2);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->Cell(18, 4, $meta[0], 0, 0, 'L');

            $pdf->SetXY(156, $yMeta + 1.2);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(30, 41, 59);
            $this->drawCellFit($pdf, 38, 4, $meta[1], 0, 0, 'L');

            $yMeta += 6.25;
        }

        // ---------------------------------------------------------
        // 3. LEMBAR TINJAUAN TITLE BAND (Y: 67 to 77) — height 10mm
        // ---------------------------------------------------------
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->Line(15, 77, 195, 77);
        // Vertically center 11pt text in 10mm band (Y=67..77)
        $pdf->SetXY(15, 69.5);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(180, 5, 'LEMBAR TINJAUAN DAN PENGESAHAN DOKUMEN', 0, 1, 'C');

        // ---------------------------------------------------------
        // 4. TABLE COLUMN HEADER ROW (Y: 77 to 85) — height 8mm
        // ---------------------------------------------------------
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->SetDrawColor(...$BLACK);
        $pdf->Rect(15, 77, 65, 8);  // Cell 1
        $pdf->Rect(80, 77, 45, 8);  // Cell 2
        $pdf->Rect(125, 77, 40, 8); // Cell 3
        $pdf->Rect(165, 77, 30, 8); // Cell 4

        $colHeaders = [
            [15,  65, 'Jabatan'],
            [80,  45, 'Kode Nama'],
            [125, 40, 'Tanda Tangan'],
            [165, 30, 'Tanggal'],
        ];
        foreach ($colHeaders as $h) {
            $pdf->SetXY($h[0], 79.0); // vertical center within 8mm band
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetTextColor(30, 41, 59);
            $pdf->Cell($h[1], 4, $h[2], 0, 0, 'C');
        }

        // ---------------------------------------------------------
        // 5. TABLE BODY ROWS
        // ---------------------------------------------------------
        $currentY = 85.0; // body starts immediately below col-header row (Y=85)
        $coordinates = [];

        $numReviewers = count($reviewers);
        $rowHeight = 12.0;
        $headerHeight = 6.0;

        if ($numReviewers > 5) {
            // Shrink row height and header height proportionally so 8 reviewers fit perfectly in 1 page.
            $rowHeight = max(8.0, 12.0 - ($numReviewers - 5) * 1.2);
            $headerHeight = max(4.5, 6.0 - ($numReviewers - 5) * 0.4);
        }

        // Helper to draw a section header row (light gray fill, black border, black text)
        $drawSectionHeader = function ($label) use ($pdf, &$currentY, $headerHeight, $LINE_TABLE, $BLACK) {
            $pdf->SetFillColor(245, 245, 245); // very light gray
            $pdf->SetLineWidth($LINE_TABLE);
            $pdf->SetDrawColor(...$BLACK);
            $pdf->Rect(15, $currentY, 180, $headerHeight, 'DF'); // Draw border and Fill background

            $pdf->SetXY(17, $currentY + ($headerHeight - 4.0) / 2.0);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(176, 4, $label, 0, 1, 'L');
            $currentY += $headerHeight;
        };

        $drawSignerRow = function (User $user) use ($pdf, &$currentY, &$coordinates, $rowHeight, $LINE_TABLE, $BLACK) {
            $pdf->SetLineWidth($LINE_TABLE);
            $pdf->SetDrawColor(...$BLACK);
            $pdf->Rect(15, $currentY, 65, $rowHeight);  // Cell 1
            $pdf->Rect(80, $currentY, 45, $rowHeight);  // Cell 2
            $pdf->Rect(125, $currentY, 40, $rowHeight); // Cell 3
            $pdf->Rect(165, $currentY, 30, $rowHeight); // Cell 4

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
        // 6. FOOTER NOTES — always visible, dark gray, italic, left-aligned
        // Anchored at Y=270 (5mm above the outer border at Y=282) so it
        // never overlaps the MASTER DOCUMENT stamp or falls outside page.
        // ---------------------------------------------------------
        $pdf->SetXY(17, $currentY + 5.0);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetTextColor(80, 80, 80); // dark gray — clearly legible
        $pdf->Cell(160, 4, 'Keterangan: NA (Not Applicable), apabila tidak diperlukan pemeriksaan dan persetujuan dari Pejabat terkait', 0, 1, 'L');

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
