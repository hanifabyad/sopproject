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
    public function generate(array $data, User $creator, $reviewers, $finalApprover): array
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
        $LINE_OUTER = 0.35; // outer page border
        $LINE_TABLE = 0.20; // all internal table lines

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
        $pdf->Line(50, 15, 50, 42);   // Logo | Company text split

        $companyMap = self::getCompanyMap();

        // Retrieve current company info, fallback to PKM Group default
        $companyCode = strtolower(trim($companyHeader));
        $companyInfo = $companyMap[$companyCode] ?? $companyMap['pkm'];

        $companyName = $companyInfo['name'];
        $companyAddress = $companyInfo['address'];
        $logoFile = $companyInfo['logo'];

        // --- LOGO (centered inside left cell: X=15..50, Y=15..42) ---
        // Cell dimensions: width=35mm, height=27mm
        // Logo rendered dynamically based on original aspect ratio to fit inside a larger bounding box
        $logoCellX = 15.0;
        $logoCellY = 15.0;
        $logoCellW = 35.0;
        $logoCellH = 27.0;

        $logoPath = public_path('img/' . $logoFile);
        if (!file_exists($logoPath)) {
            $logoPath = storage_path('app/e library archive/Logo/' . $logoFile);
        }
        if (!file_exists($logoPath)) {
            $logoPath = storage_path('app/e library archive/Logo (1)/' . $logoFile);
        }

        $logoDrawn = false;
        $defaultLogosToSkip = [
            'SCK.jpg', 'LBS.jpg', 'BKI.jpg', 'Baintan Anugerah.jpg', 'CNGM New.jpg',
            'Daya Makmur Sejahtera.jpg', 'Dumas.jpg', 'EPCM.jpg', 'Eka Daya Bahari MAs.jpg',
            'Era Kencana Laras.jpg', 'Hiswana.jpg', 'Ismadi Salam.jpg', 'LEP.jpg',
            'MMS.jpg', 'Mitha Kelana Wijaya.jpg', 'Mitra Cipta Nusa Persada.jpg', 'PIMS.jpg',
            'PKSP.jpg', 'logo RAP.jpg', 'SDRP.jpg', 'SIR.jpg', 'WIMT.jpg'
        ];

        if (in_array($companyCode, ['pkm', 'cpt'], true) || !in_array($logoFile, $defaultLogosToSkip, true)) {
            if (file_exists($logoPath) && !is_dir($logoPath)) {
                $size = @getimagesize($logoPath);
                if ($size) {
                    $imgW = $size[0];
                    $imgH = $size[1];
                    $aspect = $imgH / $imgW;

                    // Max width is 34.0mm, max height is 25.5mm (maximizes logo size in the 35x27mm cell)
                    $logoW = 34.0;
                    $logoH = $logoW * $aspect;

                    if ($logoH > 25.5) {
                        $logoH = 25.5;
                        $logoW = $logoH / $aspect;
                    }

                    $logoX = $logoCellX + ($logoCellW - $logoW) / 2.0;
                    $logoY = $logoCellY + ($logoCellH - $logoH) / 2.0;

                    $pdf->Image($logoPath, $logoX, $logoY, $logoW, $logoH);
                    $logoDrawn = true;
                }
            }
        }

        if (!$logoDrawn) {
            // Fallback text logo (no border rect, just centered text)
            $text = 'PT. ' . strtoupper($companyCode);
            
            $fontSize = 16.0;
            $pdf->SetFont('Arial', 'B', $fontSize);
            $pdf->SetTextColor(0, 0, 0); // Pure black bold text as requested
            
            // Auto-shrink font size if the text is wider than the logo cell (minus padding)
            while ($pdf->GetStringWidth($text) > ($logoCellW - 4.0) && $fontSize > 8.0) {
                $fontSize -= 0.5;
                $pdf->SetFont('Arial', 'B', $fontSize);
            }
            
            $pdf->SetXY($logoCellX, $logoCellY);
            $pdf->Cell($logoCellW, $logoCellH, $text, 0, 0, 'C');
        }

        // --- Company name & address, vertically centered in right cell ---
        if ($companyCode === 'cpt') {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('Arial', 'B', 11.0);
            $pdf->SetXY(50, 18.0);
            $pdf->Cell(145, 4.5, 'PT. CAHAYA PERDANA TRANSALAM', 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', 9.5);
            $pdf->SetXY(50, 23.5);
            $pdf->Cell(145, 4.0, 'A SUBSIDIARY OF PKM GROUP', 0, 1, 'C');

            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->SetXY(50, 28.5);
            $pdf->Cell(145, 4.0, 'Jl. K.H. Ahmad Dahlan No. 01, Kelurahan Tanjung Riau,', 0, 1, 'C');
            $pdf->SetXY(50, 32.5);
            $pdf->Cell(145, 4.0, 'Kecamatan Sekupang, Batam 29425 Prov Kepulauan Riau', 0, 1, 'C');
        } else {
            $pdf->SetTextColor(30, 41, 59);
            $pdf->SetFont('Arial', 'B', 11.0);

            // Auto-fit company name to fit cleanly inside the 145mm cell
            $companyNameSize = 11.0;
            while ($pdf->GetStringWidth($companyName) > 141 && $companyNameSize > 8.0) {
                $companyNameSize -= 0.5;
                $pdf->SetFontSize($companyNameSize);
            }

            $pdf->SetXY(50, 21.0);
            $pdf->Cell(145, 6, $companyName, 0, 1, 'C');

            $pdf->SetXY(50, 27.5);
            $pdf->SetFont('Arial', '', 11.0);
            $pdf->SetTextColor(80, 80, 80);
            $pdf->Cell(145, 4.5, $companyAddress, 0, 1, 'C');
        }

        // ---------------------------------------------------------
        // 2. TITLE & METADATA SECTION (Y: 42 to 67) — height 25mm
        // ---------------------------------------------------------
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->Line(15, 67, 195, 67);

        if ($companyCode === 'cpt') {
            $pdf->Line(135, 42, 135, 67); // Title | Meta split
        } else {
            $pdf->Line(135, 42, 135, 67); // Title | Meta split
        }

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
            16 => 8.0,
            14 => 7.0,
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
            $pdf->SetFont('Arial', '', $fs);
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
            $pdf->SetFont('Arial', '', $chosenSize);
            while ($pdf->GetStringWidth($last . '...') > $titleW && strlen($last) > 0) {
                $last = rtrim(substr($last, 0, -1));
            }
            $chosenLines[count($chosenLines) - 1] = $last . '...';
        }

        // Render: vertically center the text block inside the 25mm area
        $totalTextH = count($chosenLines) * $chosenLineH;
        $startY     = $titleAreaY + ($titleAreaH - $totalTextH) / 2.0;

        $pdf->SetFont('Arial', '', $chosenSize);
        $pdf->SetTextColor(30, 41, 59);
        foreach ($chosenLines as $line) {
            $pdf->SetXY($titleX, $startY);
            $pdf->Cell($titleW, $chosenLineH, $line, 0, 0, 'C');
            $startY += $chosenLineH;
        }

        // Metadata sub-grid lines — uniform 0.35mm black
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->SetDrawColor(...$BLACK);

        if ($companyCode === 'cpt') {
            // CPT has only 2 rows in top-right meta table (No Dok and Halaman)
            $pdf->Line(135, 54.5, 195, 54.5);
            $pdf->Line(155, 42,    155, 67);    // Label | Value split

            $metadata = [
                ['No Dok',  $docNumber],
                ['Halaman', '1 dari ' . $totalPages], // CPT has LP as page 1
            ];

            $yMeta = 42.0; // starts at top of Title+Meta section
            foreach ($metadata as $meta) {
                $pdf->SetXY(136, $yMeta + 4.25);
                $pdf->SetFont('Arial', '', 11.0);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->Cell(18, 4.5, $meta[0], 0, 0, 'L');

                $pdf->SetXY(156, $yMeta + 4.25);
                $pdf->SetFont('Arial', '', 11.0);
                $pdf->SetTextColor(30, 41, 59);
                $this->drawCellFit($pdf, 38, 4.5, $meta[1], 0, 0, 'L');

                $yMeta += 12.5; // split 25mm into 2 halves
            }
        } else {
            // Default 4 rows
            $pdf->Line(135, 48.25, 195, 48.25);
            $pdf->Line(135, 54.5,  195, 54.5);
            $pdf->Line(135, 60.75, 195, 60.75);
            $pdf->Line(155, 42,    155, 67);    // Label | Value split

            $metadata = [
                ['No Dok',  $docNumber],
                ['Revisi',  $revision],
                ['Tanggal', $docDate],
                ['Halaman', '1 dari ' . $totalPages],
            ];

            $yMeta = 42.0; // starts at top of Title+Meta section
            foreach ($metadata as $meta) {
                $pdf->SetXY(136, $yMeta + 1.2);
                $pdf->SetFont('Arial', '', 11.0);
                $pdf->SetTextColor(80, 80, 80);
                $pdf->Cell(18, 4, $meta[0], 0, 0, 'L');

                $pdf->SetXY(156, $yMeta + 1.2);
                $pdf->SetFont('Arial', '', 11.0);
                $pdf->SetTextColor(30, 41, 59);
                $this->drawCellFit($pdf, 38, 4, $meta[1], 0, 0, 'L');

                $yMeta += 6.25;
            }
        }

        // ---------------------------------------------------------
        // 3. LEMBAR TINJAUAN TITLE BAND (Y: 67 to 77) — height 10mm
        // ---------------------------------------------------------
        $pdf->SetDrawColor(...$BLACK);
        $pdf->SetLineWidth($LINE_TABLE);
        $pdf->Line(15, 77, 195, 77);
        // Vertically center 11pt text in 10mm band (Y=67..77)
        $pdf->SetXY(15, 69.5);
        $pdf->SetFont('Arial', 'B', 11.0);
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
            $pdf->SetFont('Arial', '', 11.0);
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

            $pdf->SetXY(17, $currentY + ($headerHeight - 4.5) / 2.0);
            $pdf->SetFont('Arial', 'B', 11.0);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(176, 4, $label, 0, 1, 'L');
            $currentY += $headerHeight;
        };

        $userOccurrences = [];
        $drawSignerRow = function (User $user, ?string $displayRole = null) use ($pdf, &$currentY, &$coordinates, &$userOccurrences, $rowHeight, $LINE_TABLE, $BLACK) {
            $pdf->SetLineWidth($LINE_TABLE);
            $pdf->SetDrawColor(...$BLACK);
            $pdf->Rect(15, $currentY, 65, $rowHeight);  // Cell 1
            $pdf->Rect(80, $currentY, 45, $rowHeight);  // Cell 2
            $pdf->Rect(125, $currentY, 40, $rowHeight); // Cell 3
            $pdf->Rect(165, $currentY, 30, $rowHeight); // Cell 4

            // Column 1: Jabatan / Role
            $pdf->SetTextColor(30, 41, 59);
            $roleText = $displayRole ?? ($user->role ?? '-');
            $this->drawCellWrapped($pdf, 17, $currentY, 61, $rowHeight, $roleText, 11.0, false);

            // Column 2: Kode Nama / Full Name
            $this->drawCellWrapped($pdf, 82, $currentY, 41, $rowHeight, $user->full_name ?? $user->username, 11.0, false);

            // Compute math coordinate for 100% center stamp placement
            // Signature Column starts at X=125, width=40. Center is 145.
            $centeredX = 145.0 - 12.5; // 132.50 mm (center of 40mm column)
            $centeredY = $currentY + ($rowHeight / 2.0) - 2.6; // Vertical center based on rowHeight

            // Adjust coordinates to compensate for ReviewerController's draw offsets (+7.2 X, +0.9 Y)
            $stampX = $centeredX - 7.2; 
            $stampY = $centeredY - 0.9; 

            $occurrence = $userOccurrences[$user->id] ?? 0;
            $userOccurrences[$user->id] = $occurrence + 1;
            $coordinates[] = [
                'user_id' => $user->id,
                'occurrence' => $occurrence,
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
        $drawSectionHeader($companyCode === 'cpt' ? 'Ditinjau dan diketahui oleh:' : 'Diperiksa dan Diketahui oleh:');
        foreach ($reviewers as $rev) {
            $drawSignerRow($rev);
        }

        // Section C: Disahkan oleh:
        $drawSectionHeader('Disahkan oleh:');
        $finalApprovers = $finalApprover instanceof User ? collect([$finalApprover]) : collect($finalApprover);
        foreach ($finalApprovers->values() as $finalIndex => $approver) {
            $finalRole = null;
            if ($companyCode === 'cpt' && (strtolower((string)$approver->username) === 'hendro' || $finalApprovers->count() > 1)) {
                $finalRole = $finalIndex === 0 ? 'Direktur CPT' : 'Direktur Utama';
            }
            $drawSignerRow($approver, $finalRole);
        }

        // ---------------------------------------------------------
        // 6. FOOTER NOTES & REVISION TABLE (PT CPT Specific or Default)
        // ---------------------------------------------------------
        // Draw note text below approval table (for all templates, exactly 5mm below the last signature row)
        $pdf->SetXY(17, $currentY + 5.0);
        $pdf->SetFont('Arial', '', 9.0);
        if ($companyCode === 'cpt') {
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(176, 4, 'Keterangan: NA (Not Applicable), apabila tidak diperlukan pemeriksaan dan persetujuan dari Pejabat terkait', 0, 1, 'L');
        } else {
            $pdf->SetTextColor(80, 80, 80); // dark gray — clearly legible
            $pdf->Cell(160, 4, 'Keterangan: NA (Not Applicable), apabila tidak diperlukan pemeriksaan dan persetujuan dari Pejabat terkait', 0, 1, 'L');
        }

        if ($companyCode === 'cpt') {
            $yTable = 240.0;

            // Draw table outer rect
            $pdf->SetLineWidth($LINE_TABLE);
            $pdf->SetDrawColor(...$BLACK);
            $pdf->Rect(15, $yTable, 180, 16);

            // Draw middle horizontal line (starts after label column to avoid crossing through text)
            $pdf->Line(45, $yTable + 8, 195, $yTable + 8);

            // Draw vertical line for label column
            $pdf->Line(45, $yTable, 45, $yTable + 16);

            // Draw vertical lines for the 4 columns
            $pdf->Line(82.5,  $yTable, 82.5,  $yTable + 16);
            $pdf->Line(120.0, $yTable, 120.0, $yTable + 16);
            $pdf->Line(157.5, $yTable, 157.5, $yTable + 16);

            // Draw label — vertically centered across full 16mm table height
            // The middle horizontal line is at yTable+8, so we center the label
            // across the entire label column height to avoid the line crossing through text
            $pdf->SetFont('Arial', '', 10.0);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetXY(15, $yTable);
            $pdf->Cell(30, 16, 'No/Tgl Revisi', 0, 0, 'C');

            // Draw revision cell values
            $revisionNumber = (string)((int)($data['doc_revision'] ?? 0));
            $revisionDate = $data['revision_date'] ?? $docDate;
            $revisionDate = date('d.m.y', strtotime($revisionDate));
            // Angka revisi selalu tersedia sebagai slot default. Tanggal pada slot
            // aktif diisi berdasarkan tanggal pembuatan atau tanggal revisi terbaru.
            $revValues = [
                ['0.', '1.', '2.', '3.'],
                ['4.', '5.', '6.', '7.']
            ];
            $revisionIndex = (int)$revisionNumber;
            $revisionHistory = $data['revision_history'] ?? [];
            if (!array_key_exists($revisionIndex, $revisionHistory)) {
                $revisionHistory[$revisionIndex] = $revisionDate;
            }
            foreach ($revisionHistory as $historyIndex => $historyDate) {
                $historyIndex = (int)$historyIndex;
                if ($historyIndex >= 0 && $historyIndex <= 7 && filled($historyDate)) {
                    $historyDate = date('d.m.y', strtotime($historyDate));
                    $revValues[intdiv($historyIndex, 4)][$historyIndex % 4] = $historyIndex . '. ' . $historyDate;
                }
            }

            $colWidth = 37.5;
            for ($row = 0; $row < 2; $row++) {
                $yPos = $yTable + ($row * 8) + 2.0;
                for ($col = 0; $col < 4; $col++) {
                    $xPos = 47 + ($col * $colWidth);
                    $pdf->SetXY($xPos, $yPos);
                    $pdf->SetFont('Arial', '', 10.0);
                    $pdf->Cell($colWidth - 4, 4, $revValues[$row][$col], 0, 0, 'L');
                }
            }
        }

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

    public static function getCompanyMap()
    {
        $jsonPath = storage_path('app/company_configs.json');
        if (file_exists($jsonPath)) {
            $dynamicMap = json_decode(file_get_contents($jsonPath), true);
            if (is_array($dynamicMap) && !empty($dynamicMap)) {
                return $dynamicMap;
            }
        }
        
        return [
            'pkm' => [
                'name' => 'PT PUTRA KELANA MAKMUR (PKM) GROUP',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'PKM.jpg',
            ],
            'sck' => [
                'name' => 'PT SATRIA CITRA KENCANA (SCK)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'SCK.jpg',
            ],
            'cpt' => [
                'name' => 'PT. CAHAYA PERDANA TRANSALAM',
                'address' => 'Jl. K.H. Ahmad Dahlan No. 01, Tanjung Riau, Sekupang, Batam 29425',
                'logo' => 'cpt.jpg',
            ],
            'lbs' => [
                'name' => 'PT LINTAS BINTAN SAMUDERA (LBS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'LBS.jpg',
            ],
            'bki' => [
                'name' => 'PT BINTANG KELANA INDONESIA (BKI)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'BKI.jpg',
            ],
            'bsn' => [
                'name' => 'PT BAINTAN ANUGERAH PRATAMA (BSN)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Baintan Anugerah.jpg',
            ],
            'cngm' => [
                'name' => 'PT CITRA NUSANTARA GEMILANG MAKMUR (CNGM)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'CNGM New.jpg',
            ],
            'dms' => [
                'name' => 'PT DAYA MAKMUR SEJAHTERA (DMS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Daya Makmur Sejahtera.jpg',
            ],
            'dumas' => [
                'name' => 'PT DUMAS COAL INDONESIA (DUMAS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Dumas.jpg',
            ],
            'epcm' => [
                'name' => 'PT EKA PUTRA CIPTA MANDIRI (EPCM)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'EPCM.jpg',
            ],
            'edbm' => [
                'name' => 'PT EKA DAYA BAHARI MAS (EDBM)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Eka Daya Bahari MAs.jpg',
            ],
            'ekl' => [
                'name' => 'PT ERA KENCANA LARAS (EKL)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Era Kencana Laras.jpg',
            ],
            'hiswana' => [
                'name' => 'HISWANA MIGAS',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Hiswana.jpg',
            ],
            'is' => [
                'name' => 'PT ISMADI SALAM (IS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Ismadi Salam.jpg',
            ],
            'lep' => [
                'name' => 'PT LINTAS ELOK PERSADA (LEP)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'LEP.jpg',
            ],
            'mms' => [
                'name' => 'PT MARITIM MAKMUR SEJAHTERA (MMS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'MMS.jpg',
            ],
            'mkw' => [
                'name' => 'PT MITHA KELANA WIJAYA (MKW)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Mitha Kelana Wijaya.jpg',
            ],
            'mcnp' => [
                'name' => 'PT MITRA CIPTA NUSA PERSADA (MCNP)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'Mitra Cipta Nusa Persada.jpg',
            ],
            'pims' => [
                'name' => 'PT PUTRA INDO MANDIRI SEJAHTERA (PIMS)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'PIMS.jpg',
            ],
            'pksp' => [
                'name' => 'PT PUTRA KELANA SENTOSA PRATAMA (PKSP)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'PKSP.jpg',
            ],
            'rap' => [
                'name' => 'PT RIAU ALAM PERMAI (RAP)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'logo RAP.jpg',
            ],
            'sdrp' => [
                'name' => 'PT SATRIA DARMA RAYA PERKASA (SDRP)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'SDRP.jpg',
            ],
            'sir' => [
                'name' => 'PT SATRIA INDO RAYA (SIR)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'SIR.jpg',
            ],
            'wimt' => [
                'name' => 'PT WAHANA INDAH MARITIM TANGGUH (WIMT)',
                'address' => 'Jl. Budi Kemuliaan No. 3 Seraya, Batam',
                'logo' => 'WIMT.jpg',
            ],
        ];
    }
}
