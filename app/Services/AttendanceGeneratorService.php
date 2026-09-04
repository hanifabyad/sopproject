<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class AttendanceGeneratorService
{
    /**
     * Map company code to official entity details
     */
    public static function getCompanyMap(): array
    {
        return [
            'pkm'    => [
                'name'      => 'PT. PUTRA KELANA MAKMUR (PKM)',
                'logo'      => 'logopkm.png',
                'form_code' => 'Form PKM-BS-F9/Rev 0',
            ],
            'sck'    => [
                'name'      => 'PT. SATRIA CITRA KENCANA (SCK)',
                'logo'      => 'PT. PKM Group.jpg',
                'form_code' => 'Form SCK-BS-F9/Rev 0',
            ],
            'cpt'    => [
                'name'      => 'PT. CAHAYA PETRA TRANS (CPT)',
                'logo'      => 'CPT New.jpg',
                'form_code' => 'Form CPT-BS-F9/Rev 0',
            ],
            'lbs'    => [
                'name'      => 'PT. LINTAS BINTAN SAMUDERA (LBS)',
                'logo'      => 'LBS.jpg',
                'form_code' => 'Form LBS-BS-F9/Rev 0',
            ],
            'bki'    => [
                'name'      => 'PT. BINTANG KELANA INDONESIA (BKI)',
                'logo'      => 'BKI.jpg',
                'form_code' => 'Form BKI-BS-F9/Rev 0',
            ],
            'bsn'    => [
                'name'      => 'PT. BAINTAN ANUGERAH PRATAMA (BSN)',
                'logo'      => 'Baintan Anugerah.jpg',
                'form_code' => 'Form BSN-BS-F9/Rev 0',
            ],
            'cngm'   => [
                'name'      => 'PT. CITRA NUSANTARA GEMILANG MAKMUR (CNGM)',
                'logo'      => 'CNGM New.jpg',
                'form_code' => 'Form CNGM-BS-F9/Rev 0',
            ],
            'dms'    => [
                'name'      => 'PT. DAYA MAKMUR SEJAHTERA (DMS)',
                'logo'      => 'Daya Makmur Sejahtera.jpg',
                'form_code' => 'Form DMS-BS-F9/Rev 0',
            ],
            'dumas'  => [
                'name'      => 'PT. DUMAS COAL INDONESIA (DUMAS)',
                'logo'      => 'Dumas.jpg',
                'form_code' => 'Form DUMAS-BS-F9/Rev 0',
            ],
            'epcm'   => [
                'name'      => 'PT. EKA PUTRA CIPTA MANDIRI (EPCM)',
                'logo'      => 'EPCM.jpg',
                'form_code' => 'Form EPCM-BS-F9/Rev 0',
            ],
            'edbm'   => [
                'name'      => 'PT. EKA DAYA BAHARI MAS (EDBM)',
                'logo'      => 'Eka Daya Bahari MAs.jpg',
                'form_code' => 'Form EDBM-BS-F9/Rev 0',
            ],
            'ekl'    => [
                'name'      => 'PT. ERA KENCANA LARAS (EKL)',
                'logo'      => 'Era Kencana Laras.jpg',
                'form_code' => 'Form EKL-BS-F9/Rev 0',
            ],
            'hiswana' => [
                'name'      => 'HISWANA MIGAS',
                'logo'      => 'Hiswana.jpg',
                'form_code' => 'Form HM-BS-F9/Rev 0',
            ],
            'is'     => [
                'name'      => 'PT. ISMADI SALAM (IS)',
                'logo'      => 'Ismadi Salam.jpg',
                'form_code' => 'Form IS-BS-F9/Rev 0',
            ],
            'lep'    => [
                'name'      => 'PT. LINTAS ELOK PERSADA (LEP)',
                'logo'      => 'LEP.jpg',
                'form_code' => 'Form LEP-BS-F9/Rev 0',
            ],
            'mms'    => [
                'name'      => 'PT. MARITIM MAKMUR SEJAHTERA (MMS)',
                'logo'      => 'MMS.jpg',
                'form_code' => 'Form MMS-BS-F9/Rev 0',
            ],
            'mkw'    => [
                'name'      => 'PT. MITHA KELANA WIJAYA (MKW)',
                'logo'      => 'Mitha Kelana Wijaya.jpg',
                'form_code' => 'Form MKW-BS-F9/Rev 0',
            ],
            'mcnp'   => [
                'name'      => 'PT. MITRA CIPTA NUSA PERSADA (MCNP)',
                'logo'      => 'Mitra Cipta Nusa Persada.jpg',
                'form_code' => 'Form MCNP-BS-F9/Rev 0',
            ],
            'pims'   => [
                'name'      => 'PT. PUTRA INDO MANDIRI SEJAHTERA (PIMS)',
                'logo'      => 'PIMS.jpg',
                'form_code' => 'Form PIMS-BS-F9/Rev 0',
            ],
            'pksp'   => [
                'name'      => 'PT. PUTRA KELANA SENTOSA PRATAMA (PKSP)',
                'logo'      => 'PKSP.jpg',
                'form_code' => 'Form PKSP-BS-F9/Rev 0',
            ],
            'rap'    => [
                'name'      => 'PT. RIAU ALAM PERMAI (RAP)',
                'logo'      => 'logo RAP.jpg',
                'form_code' => 'Form RAP-BS-F9/Rev 0',
            ],
            'sdrp'   => [
                'name'      => 'PT. SATRIA DARMA RAYA PERKASA (SDRP)',
                'logo'      => 'SDRP.jpg',
                'form_code' => 'Form SDRP-BS-F9/Rev 0',
            ],
            'sir'    => [
                'name'      => 'PT. SATRIA INDO RAYA (SIR)',
                'logo'      => 'SIR.jpg',
                'form_code' => 'Form SIR-BS-F9/Rev 0',
            ],
            'wimt'   => [
                'name'      => 'PT. WAHANA INDAH MARITIM TANGGUH (WIMT)',
                'logo'      => 'WIMT.jpg',
                'form_code' => 'Form WIMT-BS-F9/Rev 0',
            ],
        ];
    }


    /**
     * Generate BS Form 9 Attendance Sheet PDF.
     *
     * @param array $data [
     *   'company'          => 'pkm'|'cpt'|'sbs'|'gvi'|'lbs',
     *   'agenda'           => string (Nama Acara / Sosialisasi SOP),
     *   'doc_number'       => string (Nomor SOP),
     *   'date'             => string (Tanggal Pelaksanaan),
     *   'time'             => string (Waktu / Jam),
     *   'location'         => string (Tempat / Ruang),
     *   'speaker'          => string (Pemateri / PIC),
     *   'participants'     => array of ['name' => string, 'dept' => string],
     *   'min_rows'         => int (default 18),
     * ]
     * @return string Absolute file path to generated PDF
     */
    public function generate(array $data): string
    {
        $pdf = new Fpdi('P', 'mm', 'A4');
        $pdf->SetMargins(15, 12, 15);
        $pdf->SetAutoPageBreak(false);
        $pdf->AddPage();

        $companyKey = strtolower(trim($data['company'] ?? 'pkm'));
        $companyMap = self::getCompanyMap();
        $companyInfo = $companyMap[$companyKey] ?? $companyMap['pkm'];

        $companyName = $companyInfo['name'];
        $formCode = $companyInfo['form_code'];
        $logoFile = $companyInfo['logo'];

        $agenda = $data['agenda'] ?? 'Sosialisasi Standar Operasional Prosedur (SOP)';
        $docNumber = $data['doc_number'] ?? '';
        $dateStr = $data['date'] ?? date('d F Y');
        $timeStr = $data['time'] ?? '09:00 WIB - Selesai';
        $locationStr = $data['location'] ?? 'Ruang Rapat / Unit Kerja';
        $speakerStr = $data['speaker'] ?? '-';
        $participants = $data['participants'] ?? [];

        // ---------------------------------------------------------
        // 1. PAGE BORDER
        // ---------------------------------------------------------
        $pdf->SetDrawColor(0, 43, 92); // Deep Corporate Navy
        $pdf->SetLineWidth(0.4);
        $pdf->Rect(15, 12, 180, 273); // Outer formal box

        // ---------------------------------------------------------
        // 2. HEADER BOX (Y: 12 to 38)
        // ---------------------------------------------------------
        $pdf->SetLineWidth(0.25);
        $pdf->Line(15, 38, 195, 38); // Bottom line of header box
        $pdf->Line(55, 12, 55, 38);  // Split Logo | Title
        $pdf->Line(148, 12, 148, 38); // Split Title | Form Code & QR

        // --- Logo in Left Cell (X: 15..55, Y: 12..38) ---
        $logoPath = public_path('img/' . $logoFile);
        if (!file_exists($logoPath)) {
            $logoPath = storage_path('app/e library archive/Logo/' . $logoFile);
        }
        if (!file_exists($logoPath)) {
            $logoPath = storage_path('app/e library archive/Logo (1)/' . $logoFile);
        }

        // Entity logos to skip and use text fallback instead (same as LpGeneratorService)
        $defaultLogosToSkip = [
            'SCK.jpg', 'LBS.jpg', 'BKI.jpg', 'Baintan Anugerah.jpg', 'CNGM New.jpg',
            'Daya Makmur Sejahtera.jpg', 'Dumas.jpg', 'EPCM.jpg', 'Eka Daya Bahari MAs.jpg',
            'Era Kencana Laras.jpg', 'Hiswana.jpg', 'Ismadi Salam.jpg', 'LEP.jpg',
            'MMS.jpg', 'Mitha Kelana Wijaya.jpg', 'Mitra Cipta Nusa Persada.jpg', 'PIMS.jpg',
            'PKSP.jpg', 'logo RAP.jpg', 'SDRP.jpg', 'SIR.jpg', 'WIMT.jpg', 'PT. PKM Group.jpg'
        ];

        $logoDrawn = false;
        if (in_array($companyKey, ['pkm', 'cpt'], true) || !in_array($logoFile, $defaultLogosToSkip, true)) {
            if (file_exists($logoPath) && !is_dir($logoPath)) {
                $imgInfo = @getimagesize($logoPath);
                if ($imgInfo && $imgInfo[0] > 0 && $imgInfo[1] > 0) {
                    $imgW = $imgInfo[0];
                    $imgH = $imgInfo[1];
                    $maxW = 35.0;
                    $maxH = 21.0;
                    $ratio = min($maxW / $imgW, $maxH / $imgH);
                    $targetW = $imgW * $ratio;
                    $targetH = $imgH * $ratio;
                    $posX = 15.0 + ((55.0 - 15.0) - $targetW) / 2.0;
                    $posY = 12.0 + ((38.0 - 12.0) - $targetH) / 2.0;
                    $pdf->Image($logoPath, $posX, $posY, $targetW, $targetH);
                    $logoDrawn = true;
                }
            }
        }

        if (!$logoDrawn) {
            // Fallback text logo persis seperti Lembar Pengesahan (LpGeneratorService)
            $text = ($companyKey === 'hiswana') ? 'HISWANA MIGAS' : ('PT. ' . strtoupper($companyKey));
            $fontSize = 15.0;
            $pdf->SetFont('Arial', 'B', $fontSize);
            $pdf->SetTextColor(0, 0, 0); // Pure black bold text
            
            // Auto-shrink jika melebihi lebar cell (40mm - 4mm margin)
            while ($pdf->GetStringWidth($text) > (40.0 - 4.0) && $fontSize > 8.0) {
                $fontSize -= 0.5;
                $pdf->SetFont('Arial', 'B', $fontSize);
            }
            
            $pdf->SetXY(15.0, 12.0);
            $pdf->Cell(40.0, 26.0, $text, 0, 0, 'C');
        }

        // --- Center Cell: Title & Company (X: 55..148) ---
        $pdf->SetXY(55, 16);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetTextColor(0, 43, 92); // Deep Navy
        $pdf->Cell(93, 7, 'FORMULIR DAFTAR HADIR', 0, 1, 'C');
        $pdf->SetXY(55, 25);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetTextColor(22, 119, 184); // Signature Corporate Blue
        $pdf->Cell(93, 6, $companyName, 0, 0, 'C');
        $pdf->SetTextColor(0, 0, 0);

        // --- Right Cell: Form Code (X: 148..195, Y: 12..38) ---
        $pdf->SetXY(148, 17);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(71, 85, 105);
        $pdf->Cell(47, 4.5, 'KODE DOKUMEN:', 0, 1, 'C');
        $pdf->SetX(148);
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetTextColor(22, 119, 184); // Signature Corporate Blue
        $pdf->Cell(47, 5.5, $formCode, 0, 1, 'C');
        $pdf->SetTextColor(0, 0, 0);

        // ---------------------------------------------------------
        // 3. METADATA SECTION (Y: 38 to 64) with Soft Blue Tint
        // ---------------------------------------------------------
        $pdf->SetFillColor(240, 249, 255); // #f0f9ff Soft Corporate Blue Tint
        $pdf->Rect(15.2, 38.2, 179.6, 25.6, 'F');

        $pdf->SetDrawColor(0, 43, 92);
        $pdf->SetLineWidth(0.25);
        $pdf->Line(15, 64, 195, 64); // Bottom of metadata
        $pdf->Line(128, 38, 128, 64); // Vertical separator at 128mm

        // Helper to guarantee text never crosses the vertical separator line
        $fitText = function($text, $maxW, $isBold = false, $fontSize = 9.0) use ($pdf) {
            $pdf->SetFont('Arial', $isBold ? 'B' : '', $fontSize);
            if ($pdf->GetStringWidth($text) <= $maxW) {
                return $text;
            }
            while ($pdf->GetStringWidth($text . '...') > $maxW && mb_strlen($text) > 3) {
                $text = mb_substr($text, 0, -1);
            }
            return $text . '...';
        };

        // ROW 1 (Y: 41.0): Agenda / Acara (Left) & Tanggal (Right)
        $pdf->SetXY(17, 41.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(0, 43, 92); // Deep Navy Label
        $pdf->Cell(28, 5.0, 'Agenda / Acara', 0, 0, 'L');
        $pdf->Cell(3, 5.0, ':', 0, 0, 'L');
        $fullAgenda = $agenda . ($docNumber ? " ({$docNumber})" : '');
        $agendaFormatted = $fitText($fullAgenda, 78.0, true, 9.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(15, 23, 42); // Dark Charcoal Text
        $pdf->Cell(78, 5.0, $agendaFormatted, 0, 0, 'L');

        // Right Row 1: Tanggal
        $pdf->SetXY(131, 41.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(0, 43, 92);
        $pdf->Cell(17, 5.0, 'Tanggal', 0, 0, 'L');
        $pdf->Cell(3, 5.0, ':', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(43, 5.0, $fitText($dateStr, 42.0, true, 9.0), 0, 1, 'L');

        // ROW 2 (Y: 48.0): Pemateri / PIC (Left) & Waktu (Right)
        $pdf->SetXY(17, 48.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(0, 43, 92);
        $pdf->Cell(28, 5.0, 'Pemateri / PIC', 0, 0, 'L');
        $pdf->Cell(3, 5.0, ':', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.0);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(78, 5.0, $fitText($speakerStr, 78.0, false, 9.0), 0, 0, 'L');

        // Right Row 2: Waktu
        $pdf->SetXY(131, 48.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(0, 43, 92);
        $pdf->Cell(17, 5.0, 'Waktu', 0, 0, 'L');
        $pdf->Cell(3, 5.0, ':', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.0);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(43, 5.0, $fitText($timeStr, 42.0, false, 9.0), 0, 1, 'L');

        // ROW 3 (Y: 55.0): Tempat / Lokasi (Left)
        $pdf->SetXY(17, 55.0);
        $pdf->SetFont('Arial', 'B', 9.0);
        $pdf->SetTextColor(0, 43, 92);
        $pdf->Cell(28, 5.0, 'Tempat / Lokasi', 0, 0, 'L');
        $pdf->Cell(3, 5.0, ':', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 9.0);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(78, 5.0, $fitText($locationStr, 78.0, false, 9.0), 0, 1, 'L');

        // ---------------------------------------------------------
        // 4. PARTICIPANTS TABLE HEADER (Y: 64 to 72) - Bold Corporate Blue
        // ---------------------------------------------------------
        $tableY = 64.0;
        $pdf->SetFillColor(22, 119, 184); // Brand Corporate Blue #1677B8
        $pdf->SetTextColor(255, 255, 255); // Crisp White Header Text
        $pdf->SetFont('Arial', 'B', 9.5);
        $pdf->SetDrawColor(0, 43, 92);

        // Columns: No (12mm) | Nama Peserta (72mm) | Dept / Bagian / Jabatan (56mm) | Status Presensi (40mm)
        $pdf->SetXY(15, $tableY);
        $pdf->Cell(12, 8, 'NO', 1, 0, 'C', true);
        $pdf->Cell(72, 8, 'NAMA PESERTA', 1, 0, 'C', true);
        $pdf->Cell(56, 8, 'DEPT / BAGIAN / JABATAN', 1, 0, 'C', true);
        $pdf->Cell(40, 8, 'STATUS PRESENSI', 1, 1, 'C', true);

        // ---------------------------------------------------------
        // 5. PARTICIPANTS ROWS (Minimum 18 rows) with Zebra Striping
        // ---------------------------------------------------------
        $minRows = $data['min_rows'] ?? 18;
        $totalRows = max($minRows, count($participants));
        $rowHeight = 10.5; // 10.5mm per row -> 18 rows = ~189mm (Y: 72..261)
        $currentY = $tableY + 8.0;

        for ($i = 0; $i < $totalRows; $i++) {
            $num = $i + 1;
            $p = $participants[$i] ?? null;
            $pName = $p['name'] ?? '';
            $pDept = $p['department'] ?? ($p['dept'] ?? '');
            $pStatus = $p['status'] ?? (!empty($pName) ? 'Hadir' : '');
            $attendedAt = $p['attended_at'] ?? null;

            // Zebra Striping: alternate white and subtle light slate
            if ($i % 2 === 1) {
                $pdf->SetFillColor(248, 250, 252); // #f8fafc
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }

            $pdf->SetXY(15, $currentY);
            $pdf->SetFont('Arial', 'B', 9.5);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(12, $rowHeight, $num, 1, 0, 'C', true);

            $pdf->SetFont('Arial', !empty($pName) ? 'B' : '', 9.5);
            $pdf->SetTextColor(15, 23, 42); // High contrast text
            $pdf->Cell(72, $rowHeight, !empty($pName) ? ('  ' . substr($pName, 0, 38)) : '', 1, 0, 'L', true);

            $pdf->SetFont('Arial', '', 9.5);
            $pdf->SetTextColor(51, 65, 85);
            $pdf->Cell(56, $rowHeight, !empty($pDept) ? ('  ' . substr($pDept, 0, 32)) : '', 1, 0, 'L', true);

            // Status Column: "Hadir" in bold emerald green
            if (!empty($pName)) {
                $pdf->SetFont('Arial', 'B', 9.5);
                $pdf->SetTextColor(4, 120, 87); // High contrast Emerald Green
                $statusText = 'HADIR';
                if ($attendedAt) {
                    $timeFormatted = is_string($attendedAt) ? date('H:i', strtotime($attendedAt)) : $attendedAt->format('H:i');
                    $statusText .= ' (' . $timeFormatted . ')';
                }
                $pdf->Cell(40, $rowHeight, $statusText, 1, 0, 'C', true);
            } else {
                $pdf->Cell(40, $rowHeight, '', 1, 0, 'C', true);
            }
            $pdf->SetTextColor(0, 0, 0);

            $currentY += $rowHeight;

            // Handle multi-page if rows exceed single page
            if ($currentY > 265 && $i < $totalRows - 1) {
                $pdf->AddPage();
                $pdf->SetDrawColor(0, 43, 92);
                $pdf->SetLineWidth(0.4);
                $pdf->Rect(15, 12, 180, 273);
                $tableY = 18.0;
                $pdf->SetFillColor(22, 119, 184);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Arial', 'B', 9.5);
                $pdf->SetXY(15, $tableY);
                $pdf->Cell(12, 8, 'NO', 1, 0, 'C', true);
                $pdf->Cell(72, 8, 'NAMA PESERTA (Lanjutan)', 1, 0, 'C', true);
                $pdf->Cell(56, 8, 'DEPT / BAGIAN / JABATAN', 1, 0, 'C', true);
                $pdf->Cell(40, 8, 'STATUS PRESENSI', 1, 1, 'C', true);
                $pdf->SetTextColor(0, 0, 0);
                $currentY = $tableY + 8.0;
            }
        }

        // ---------------------------------------------------------
        // 6. FORM FOOTER NOTES
        // ---------------------------------------------------------
        $pdf->SetDrawColor(203, 213, 225);
        $pdf->SetLineWidth(0.2);
        $pdf->Line(15, 278, 195, 278);

        $pdf->SetXY(16, 280);
        $pdf->SetFont('Arial', 'I', 8.5);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(90, 4, 'Tanggal Efektif: 21 Juni 2010', 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(22, 119, 184);
        $pdf->Cell(89, 4, $formCode, 0, 0, 'R');
        $pdf->SetTextColor(0, 0, 0);

        // Output and save PDF to storage/app/public/socializations/generated_attendance/
        $fileName = 'daftar_hadir_' . time() . '_' . uniqid() . '.pdf';
        $relPath = 'socializations/generated_attendance/' . $fileName;
        $absPath = storage_path('app/public/' . $relPath);

        if (!is_dir(dirname($absPath))) {
            mkdir(dirname($absPath), 0755, true);
        }

        $pdf->Output('F', $absPath);

        return $absPath;
    }
}
