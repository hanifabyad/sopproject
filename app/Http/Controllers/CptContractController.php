<?php

namespace App\Http\Controllers;

use App\Models\CptContract;
use App\Console\Commands\CheckExpiredCptContracts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use SimpleXMLElement;
use DateTime;

class CptContractController extends Controller
{
    /**
     * Pastikan hanya Staf PIC CPT yang ditunjuk yang dapat mengakses data kontrak
     */
    private function authorizePic(): void
    {
        $user = Auth::user();
        if (!$user || !$user->can_manage_cpt_contracts) {
            abort(403, 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat atau mengelola Register Kontrak CPT.');
        }
    }

    /**
     * Simpan data Kontrak / SPMP baru BU CPT
     */
    public function store(Request $request)
    {
        $this->authorizePic();

        $request->validate([
            'customer'       => 'required|string|max:255',
            'type'           => 'nullable|string|max:150',
            'contract_type'  => 'nullable|string|max:150',
            'project_title'  => 'nullable|string|max:1000',
            'project_name'   => 'required|string|max:255',
            'project_number' => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'status'         => 'required|in:active,expired,still_not_yet,completed',
            'notes'          => 'nullable|string|max:3000',
            'document_file'  => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:30720',
            'document_link'  => 'nullable|url|max:2000',
        ]);

        $filePath = null;
        if ($request->hasFile('document_file')) {
            $filePath = $request->file('document_file')->store('cpt_contracts/documents', 'public');
        }

        $contractType = $request->contract_type ?: $request->type;

        $contract = CptContract::create([
            'customer'       => $request->customer,
            'contract_type'  => $contractType,
            'project_title'  => $request->project_title,
            'project_name'   => $request->project_name,
            'project_number' => $request->project_number,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'status'         => $request->status,
            'notes'          => $request->notes,
            'document_file'  => $filePath,
            'document_link'  => $request->document_link,
            'created_by'     => Auth::id(),
        ]);

        if ($contract->status === 'expired' || ($contract->end_date && $contract->end_date->format('Y-m-d') <= date('Y-m-d') && $contract->status !== 'completed')) {
            CheckExpiredCptContracts::processExpiredNotifications($contract->id, true);
        }

        return redirect()->back()->with('success', "Data Kontrak/SPMP '{$contract->project_name}' berhasil ditambahkan ke Register CPT.");
    }

    /**
     * Perbarui data Kontrak / SPMP BU CPT
     */
    public function update(Request $request, $id)
    {
        $this->authorizePic();

        $contract = CptContract::findOrFail($id);

        $request->validate([
            'customer'       => 'required|string|max:255',
            'type'           => 'nullable|string|max:150',
            'contract_type'  => 'nullable|string|max:150',
            'project_title'  => 'nullable|string|max:1000',
            'project_name'   => 'required|string|max:255',
            'project_number' => 'nullable|string|max:255',
            'start_date'     => 'nullable|date',
            'end_date'       => 'nullable|date',
            'status'         => 'required|in:active,expired,still_not_yet,completed',
            'notes'          => 'nullable|string|max:3000',
            'document_file'  => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:30720',
            'document_link'  => 'nullable|url|max:2000',
        ]);

        $contractType = $request->contract_type ?: $request->type;

        $dataToUpdate = [
            'customer'       => $request->customer,
            'contract_type'  => $contractType,
            'project_title'  => $request->project_title,
            'project_name'   => $request->project_name,
            'project_number' => $request->project_number,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'status'         => $request->status,
            'notes'          => $request->notes,
            'document_link'  => $request->document_link,
        ];

        if ($request->hasFile('document_file')) {
            if ($contract->document_file && Storage::disk('public')->exists($contract->document_file)) {
                Storage::disk('public')->delete($contract->document_file);
            }
            $dataToUpdate['document_file'] = $request->file('document_file')->store('cpt_contracts/documents', 'public');
        }

        $contract->update($dataToUpdate);

        if ($contract->status === 'expired' || ($contract->end_date && $contract->end_date->format('Y-m-d') <= date('Y-m-d') && $contract->status !== 'completed')) {
            CheckExpiredCptContracts::processExpiredNotifications($contract->id, true);
        }

        return redirect()->back()->with('success', "Data Kontrak/SPMP '{$contract->project_name}' berhasil diperbarui.");
    }

    /**
     * Hapus data Kontrak BU CPT
     */
    public function destroy($id)
    {
        $this->authorizePic();

        $contract = CptContract::findOrFail($id);

        if ($contract->document_file && Storage::disk('public')->exists($contract->document_file)) {
            Storage::disk('public')->delete($contract->document_file);
        }

        $projectName = $contract->project_name;
        $contract->delete();

        return redirect()->back()->with('success', "Data Kontrak/SPMP '{$projectName}' berhasil dihapus dari sistem.");
    }

    /**
     * Stream berkas PDF dokumen kontrak yang diunggah
     */
    public function viewDocument($id)
    {
        $this->authorizePic();

        $contract = CptContract::findOrFail($id);

        if ($contract->document_file && Storage::disk('public')->exists($contract->document_file)) {
            $path = storage_path('app/public/' . $contract->document_file);
            return response()->file($path, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            ]);
        }

        if ($contract->document_link) {
            return redirect()->away($contract->document_link);
        }

        abort(404, 'Berkas dokumen tidak ditemukan.');
    }

    /**
     * Download Template Excel / CSV untuk Import Kontrak CPT
     */
    public function downloadTemplate(Request $request)
    {
        $headers = [
            'No',
            'Customer',
            'Type',
            'Project Title',
            'Project Name',
            'Project Number',
            'Start Date',
            'End Date',
            'Status',
            'Note',
            'Document Link',
        ];

        $sampleData = [
            [
                '1',
                'PT Patra Logistik',
                'Kontrak',
                'Jasa Transportir Angkutan BBM Laut untuk Layanan VHS PT Timah Tbk',
                'Timah',
                'KTR-457/PL000010/2023-S0',
                '2023-08-01',
                '2024-12-31',
                'active',
                'Kontrak sedang berjalan',
                'https://drive.google.com/file/d/sample1/view',
            ],
            [
                '2',
                'PT Patra Logistik',
                'Addendum',
                'Pengangkutan Jasa Transportir Bahan Bakar Minyak (BBM) PT PLN UP 3 Dumai',
                'PLN UP 3 Dumai',
                'KTR-118/PL000010/2023-S0',
                '2023-07-01',
                '2023-12-31',
                'expired',
                'Menunggu penerbitan kontrak baru',
                'https://drive.google.com/file/d/sample2/view',
            ],
            [
                '3',
                'PT Patra Logistik',
                'Surat Perintah Memulai Pekerjaan (SPMP)',
                'Jasa Layanan Franco Angkutan BBM Bea Cukai Karimun',
                'Shoretonk - Bea Cukai',
                'SPMP-002/PL100110/2024-S3',
                '2024-01-01',
                '',
                'still_not_yet',
                'Telah disubmit PPHK',
                'https://drive.google.com/file/d/sample3/view',
            ],
        ];

        $format = strtolower($request->query('format', 'excel'));

        if ($format === 'csv') {
            $filename = 'Template_Register_Kontrak_CPT_' . date('Ymd') . '.csv';
            $callback = function () use ($headers, $sampleData) {
                $file = fopen('php://output', 'w');
                // Add UTF-8 BOM for Microsoft Excel compatibility
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($file, $headers);

                foreach ($sampleData as $row) {
                    fputcsv($file, $row);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Default: Native Styled Microsoft Excel Spreadsheet (.xls)
        $filename = 'Template_Register_Kontrak_CPT_' . date('Ymd') . '.xls';
        $colWidths = [35, 130, 90, 260, 120, 150, 85, 85, 90, 180, 220];

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<?mso-application progid=\"Excel.Sheet\"?>\n";
        $xml .= "<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= " xmlns:o=\"urn:schemas-microsoft-com:office:office\"\n";
        $xml .= " xmlns:x=\"urn:schemas-microsoft-com:office:excel\"\n";
        $xml .= " xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"\n";
        $xml .= " xmlns:html=\"http://www.w3.org/TR/REC-html40\">\n";
        $xml .= " <Styles>\n";
        $xml .= "  <Style ss:ID=\"Header\">\n";
        $xml .= "   <Font ss:Bold=\"1\" ss:Color=\"#FFFFFF\" ss:FontName=\"Calibri\" ss:Size=\"11\"/>\n";
        $xml .= "   <Interior ss:Color=\"#1677B8\" ss:Pattern=\"Solid\"/>\n";
        $xml .= "   <Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\" ss:WrapText=\"1\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#CBD5E1\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "  </Style>\n";
        $xml .= "  <Style ss:ID=\"Data\">\n";
        $xml .= "   <Font ss:Color=\"#0F172A\" ss:FontName=\"Calibri\" ss:Size=\"10\"/>\n";
        $xml .= "   <Alignment ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "  </Style>\n";
        $xml .= "  <Style ss:ID=\"DataCenter\">\n";
        $xml .= "   <Font ss:Color=\"#0F172A\" ss:FontName=\"Calibri\" ss:Size=\"10\"/>\n";
        $xml .= "   <Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>\n";
        $xml .= "   <Borders>\n";
        $xml .= "    <Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "    <Border ss:Position=\"Top\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\" ss:Color=\"#E2E8F0\"/>\n";
        $xml .= "   </Borders>\n";
        $xml .= "  </Style>\n";
        $xml .= " </Styles>\n";
        $xml .= " <Worksheet ss:Name=\"Register Kontrak CPT\">\n";
        $xml .= "  <Table>\n";
        
        foreach ($colWidths as $w) {
            $xml .= "   <Column ss:Width=\"{$w}\"/>\n";
        }

        // Header Row
        $xml .= "   <Row ss:Height=\"26\">\n";
        foreach ($headers as $h) {
            $safeH = htmlspecialchars($h, ENT_XML1, 'UTF-8');
            $xml .= "    <Cell ss:StyleID=\"Header\"><Data ss:Type=\"String\">{$safeH}</Data></Cell>\n";
        }
        $xml .= "   </Row>\n";

        // Data Rows
        foreach ($sampleData as $row) {
            $xml .= "   <Row ss:Height=\"22\">\n";
            foreach ($row as $i => $val) {
                $safeVal = htmlspecialchars((string)$val, ENT_XML1, 'UTF-8');
                $styleId = ($i === 0 || $i === 6 || $i === 7 || $i === 8) ? 'DataCenter' : 'Data';
                $xml .= "    <Cell ss:StyleID=\"{$styleId}\"><Data ss:Type=\"String\">{$safeVal}</Data></Cell>\n";
            }
            $xml .= "   </Row>\n";
        }

        $xml .= "  </Table>\n";
        $xml .= " </Worksheet>\n";
        $xml .= "</Workbook>\n";

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Import Data Kontrak CPT dari file Excel (.xlsx, .xls) atau CSV (.csv)
     */
    public function import(Request $request)
    {
        $this->authorizePic();

        $request->validate([
            'file' => 'required|file|max:20480', // max 20MB
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();

        $rows = [];

        try {
            if (in_array($extension, ['csv', 'txt'])) {
                $rows = $this->parseCsv($path);
            } elseif ($extension === 'xls') {
                $rows = $this->parseXls($path);
            } elseif ($extension === 'xlsx') {
                if (class_exists('ZipArchive')) {
                    try {
                        $rows = $this->parseXlsx($path);
                    } catch (\Throwable $ze) {
                        $rows = $this->parseCsv($path);
                    }
                } else {
                    $rows = $this->parseCsv($path);
                }
            } else {
                // Try XLS or CSV fallback
                $rows = $this->parseXls($path);
                if (empty($rows)) {
                    $rows = $this->parseCsv($path);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Gagal membaca file import kontrak: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return redirect()->back()->with('error', 'File import kosong atau tidak memiliki data yang valid.');
        }

        // Header mapping
        $headerRow = array_shift($rows);
        $columnIndexMap = $this->mapHeaderColumns($headerRow);

        $importedCount = 0;
        $adminId = Auth::id() ?: 1;

        foreach ($rows as $row) {
            // Skip completely empty rows
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $customer      = $this->getColumnValue($row, $columnIndexMap, 'customer') ?: 'PT Patra Logistik';
            $contractType  = $this->getColumnValue($row, $columnIndexMap, 'type') ?: 'Kontrak';
            $projectTitle  = $this->getColumnValue($row, $columnIndexMap, 'project_title');
            $projectName   = $this->getColumnValue($row, $columnIndexMap, 'project_name');
            $projectNumber = $this->getColumnValue($row, $columnIndexMap, 'project_number');
            $startDateRaw  = $this->getColumnValue($row, $columnIndexMap, 'start_date');
            $endDateRaw    = $this->getColumnValue($row, $columnIndexMap, 'end_date');
            $statusRaw     = $this->getColumnValue($row, $columnIndexMap, 'status');
            $notes         = $this->getColumnValue($row, $columnIndexMap, 'notes');
            $documentLink  = $this->getColumnValue($row, $columnIndexMap, 'document_link');

            if (empty($projectName) && empty($projectTitle)) {
                continue;
            }

            if (empty($projectName)) {
                $projectName = mb_substr($projectTitle, 0, 50);
            }

            $startDate = $this->parseDateValue($startDateRaw);
            $endDate   = $this->parseDateValue($endDateRaw);
            $status    = $this->normalizeStatus($statusRaw);

            CptContract::updateOrCreate(
                [
                    'project_name'   => $projectName,
                    'project_number' => $projectNumber ?: '-',
                ],
                [
                    'customer'       => $customer,
                    'contract_type'  => $contractType,
                    'project_title'  => $projectTitle ?: $projectName,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'status'         => $status,
                    'notes'          => $notes,
                    'document_link'  => $documentLink ?: null,
                    'created_by'     => $adminId,
                ]
            );

            $importedCount++;
        }

        // Jalankan pengecekan otomatis & kirim notif email untuk kontrak yang terdeteksi expired dari file import
        CheckExpiredCptContracts::processExpiredNotifications();

        return redirect()->back()->with('success', "✨ Berhasil mengimpor {$importedCount} data kontrak & SPMP ke Register BU CPT.");
    }

    /**
     * Parse CSV / Delimited text file
     */
    private function parseCsv(string $filePath): array
    {
        $rows = [];
        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        // Check for delimiter (; or , or \t)
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = ',';
        if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
            $delimiter = ';';
        } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
            $delimiter = "\t";
        }

        while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
            $rows[] = array_map(function($val) {
                return trim(preg_replace('/^[\xEF\xBB\xBF\s]+|[\s]+$/u', '', (string)$val));
            }, $data);
        }
        fclose($handle);
        return $rows;
    }

    /**
     * Parse XLSX file natively using ZipArchive + SimpleXMLElement
     */
    private function parseXlsx(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \Exception('Tidak dapat membuka file XLSX.');
        }

        // 1. Read shared strings
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml) {
            $xml = new SimpleXMLElement($sharedStringsXml);
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $t = '';
                    foreach ($si->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $sharedStrings[] = $t;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        // 2. Read sheet1
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if (!$sheetXml) {
            // Try searching for any sheet in xl/worksheets
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (str_starts_with($filename, 'xl/worksheets/sheet') && str_ends_with($filename, '.xml')) {
                    $sheetXml = $zip->getFromName($filename);
                    break;
                }
            }
        }
        $zip->close();

        if (!$sheetXml) {
            throw new \Exception('Sheet tidak ditemukan di file XLSX.');
        }

        $xml = new SimpleXMLElement($sheetXml);
        $rows = [];

        if (isset($xml->sheetData->row)) {
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                $colIndex = 0;

                foreach ($row->c as $c) {
                    // Cell reference like A1, B1, etc.
                    $cellRef = (string)$c['r'];
                    $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                    $targetColIndex = $this->columnLetterToIndex($colLetters);

                    // Pad empty cells if any
                    while ($colIndex < $targetColIndex) {
                        $rowData[] = '';
                        $colIndex++;
                    }

                    $cellType = (string)$c['t'];
                    $value = isset($c->v) ? (string)$c->v : '';

                    if ($cellType === 's' && isset($sharedStrings[(int)$value])) {
                        $value = $sharedStrings[(int)$value];
                    } elseif ($cellType === 'inlineStr' && isset($c->is->t)) {
                        $value = (string)$c->is->t;
                    }

                    $rowData[] = trim($value);
                    $colIndex++;
                }

                $rows[] = $rowData;
            }
        }

        return $rows;
    }

    /**
     * Parse XLS file (supports XML Spreadsheet & HTML Table format)
     */
    private function parseXls(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if (!$content) return [];

        $rows = [];

        // 1. Check for XML Spreadsheet 2003 format
        if (str_contains($content, 'urn:schemas-microsoft-com:office:spreadsheet') || str_contains($content, '<Workbook')) {
            try {
                // Remove encoding headers if any conflict
                $cleanXml = preg_replace('/<\?xml[^\>]*\?>/i', '', $content);
                $cleanXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $cleanXml;
                
                $xml = simplexml_load_string($cleanXml);
                if ($xml !== false) {
                    $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
                    $xmlRows = $xml->xpath('//ss:Row');

                    if (!empty($xmlRows)) {
                        foreach ($xmlRows as $r) {
                            $r->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
                            $cells = $r->xpath('ss:Cell');
                            $rowData = [];
                            foreach ($cells as $c) {
                                $c->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
                                $data = $c->xpath('ss:Data');
                                $rowData[] = trim((string)($data[0] ?? ''));
                            }
                            if (!empty(array_filter($rowData, fn($v) => $v !== ''))) {
                                $rows[] = $rowData;
                            }
                        }
                        if (!empty($rows)) {
                            return $rows;
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal parse XML Spreadsheet: ' . $e->getMessage());
            }
        }

        // 2. Check for HTML Table format (.xls from web exports)
        if (str_contains($content, '<table') || str_contains($content, '<tr')) {
            try {
                $dom = new \DOMDocument();
                @$dom->loadHTML('<?xml encoding="UTF-8">' . $content);
                $tableRows = $dom->getElementsByTagName('tr');

                foreach ($tableRows as $tr) {
                    $rowData = [];
                    $cells = $tr->getElementsByTagName('td');
                    if ($cells->length === 0) {
                        $cells = $tr->getElementsByTagName('th');
                    }
                    foreach ($cells as $cell) {
                        $rowData[] = trim($cell->textContent);
                    }
                    if (!empty(array_filter($rowData, fn($v) => $v !== ''))) {
                        $rows[] = $rowData;
                    }
                }
                if (!empty($rows)) {
                    return $rows;
                }
            } catch (\Throwable $e) {
                Log::warning('Gagal parse HTML table XLS: ' . $e->getMessage());
            }
        }

        // 3. Fallback to CSV parser
        return $this->parseCsv($filePath);
    }

    /**
     * Convert column letter (A, B, AA) to 0-based index
     */
    private function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $length = strlen($letters);
        $index = 0;
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }

    /**
     * Map Header row to standard keys
     */
    private function mapHeaderColumns(array $headers): array
    {
        $map = [];
        foreach ($headers as $idx => $h) {
            $clean = strtolower(trim((string)$h));
            $clean = preg_replace('/[^a-z0-9]/', '', $clean);

            if (str_contains($clean, 'customer') || str_contains($clean, 'pelanggan') || str_contains($clean, 'klien')) {
                $map['customer'] = $idx;
            } elseif (str_contains($clean, 'type') || str_contains($clean, 'tipe') || str_contains($clean, 'jenis')) {
                $map['type'] = $idx;
            } elseif (str_contains($clean, 'projecttitle') || str_contains($clean, 'judulpekerjaan') || str_contains($clean, 'judulproyek') || str_contains($clean, 'pekerjaan')) {
                $map['project_title'] = $idx;
            } elseif (str_contains($clean, 'projectname') || str_contains($clean, 'namaproyek') || str_contains($clean, 'proyek')) {
                $map['project_name'] = $idx;
            } elseif (str_contains($clean, 'projectnumber') || str_contains($clean, 'nokontrak') || str_contains($clean, 'nomorkontrak') || str_contains($clean, 'nospmp') || str_contains($clean, 'number')) {
                $map['project_number'] = $idx;
            } elseif (str_contains($clean, 'startdate') || str_contains($clean, 'tglmulai') || str_contains($clean, 'tanggalmulai') || str_contains($clean, 'start')) {
                $map['start_date'] = $idx;
            } elseif (str_contains($clean, 'enddate') || str_contains($clean, 'tglselesai') || str_contains($clean, 'tglberakhir') || str_contains($clean, 'tanggalselesai') || str_contains($clean, 'end')) {
                $map['end_date'] = $idx;
            } elseif (str_contains($clean, 'status')) {
                $map['status'] = $idx;
            } elseif (str_contains($clean, 'note') || str_contains($clean, 'catatan') || str_contains($clean, 'keterangan')) {
                $map['notes'] = $idx;
            } elseif (str_contains($clean, 'document') || str_contains($clean, 'link') || str_contains($clean, 'dokumen') || str_contains($clean, 'drive')) {
                $map['document_link'] = $idx;
            }
        }
        return $map;
    }

    /**
     * Safely get column value from row
     */
    private function getColumnValue(array $row, array $map, string $key): ?string
    {
        if (isset($map[$key]) && isset($row[$map[$key]])) {
            $val = trim((string)$row[$map[$key]]);
            return $val !== '' ? $val : null;
        }
        return null;
    }

    /**
     * Normalize status string to enum
     */
    private function normalizeStatus(?string $status): string
    {
        if (!$status) return 'active';
        $s = strtolower(trim($status));
        $s = str_replace(['_', '-'], ' ', $s);

        if (str_contains($s, 'active') || str_contains($s, 'aktif') || str_contains($s, 'jalan')) {
            return 'active';
        }
        if (str_contains($s, 'expired') || str_contains($s, 'kadaluarsa') || str_contains($s, 'habis')) {
            return 'expired';
        }
        if (str_contains($s, 'not yet') || str_contains($s, 'still') || str_contains($s, 'proses') || str_contains($s, 'awal') || str_contains($s, 'tahap')) {
            return 'still_not_yet';
        }
        if (str_contains($s, 'completed') || str_contains($s, 'selesai') || str_contains($s, 'done')) {
            return 'completed';
        }

        return 'active';
    }

    /**
     * Parse date string or Excel serial date
     */
    private function parseDateValue(?string $val): ?string
    {
        if (!$val) return null;
        $val = trim($val);
        if ($val === '-' || $val === '' || $val === '0000-00-00') return null;

        // Numeric Excel date serial (e.g., 45139)
        if (is_numeric($val) && (float)$val > 1000 && (float)$val < 100000) {
            $days = (int)$val - 25569;
            return date('Y-m-d', $days * 86400);
        }

        $formats = [
            'Y-m-d',
            'd/m/Y',
            'm/d/Y',
            'd-m-Y',
            'Y/m/d',
            'd M Y',
            'd F Y',
            'j M Y',
            'j F Y',
            'Y.m.d',
            'd.m.Y',
        ];

        foreach ($formats as $fmt) {
            $d = DateTime::createFromFormat($fmt, $val);
            if ($d && $d->format($fmt) === $val) {
                return $d->format('Y-m-d');
            }
        }

        // Try strtotime
        $ts = strtotime($val);
        if ($ts && $ts > 0) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    /**
     * Tetapkan / Berikan hak akses PIC Staf CPT secara dinamis (Hanya Admin)
     */
    public function assignPic(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Hanya Admin yang dapat mengatur hak akses Staf PIC CPT.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Reset all existing PIC permissions first (ensure single designated PIC or explicit toggle)
        \App\Models\User::query()->update(['can_manage_cpt_contracts' => false]);

        $assignedUser = \App\Models\User::findOrFail($request->user_id);
        $assignedUser->update(['can_manage_cpt_contracts' => true]);

        return redirect()->back()->with('success', "✨ Hak akses kelola Register Kontrak CPT berhasil diberikan kepada: {$assignedUser->full_name} ({$assignedUser->username}).");
    }

    /**
     * Kirim email notifikasi kontrak expired untuk 1 data kontrak spesifik ke PIC Staf CPT
     */
    public function notifyExpired(Request $request, $id)
    {
        $contract = CptContract::findOrFail($id);

        $picUsers = \App\Models\User::where('can_manage_cpt_contracts', true)->whereNotNull('email')->get();
        if ($picUsers->isEmpty()) {
            $picUsers = \App\Models\User::where('role', 'like', '%CPT%')->whereNotNull('email')->get();
            if ($picUsers->isEmpty()) {
                $picUsers = \App\Models\User::where('role', 'admin')->whereNotNull('email')->get();
            }
        }

        if ($picUsers->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada email staf PIC CPT yang terdaftar di sistem.');
        }

        $editUrl = route('library.index', [
            'category'         => 'divisi',
            'div'              => 'KOMERSIL',
            'bu'               => 'CPT & MHM',
            'tab'              => 'contracts',
            'edit_contract_id' => $contract->id,
        ]);

        $sentNames = [];
        foreach ($picUsers as $pic) {
            try {
                \Illuminate\Support\Facades\Mail::to($pic->email)->queue(new \App\Mail\CptContractExpiredMail($contract, $pic, $editUrl));
                $sentNames[] = $pic->username;
            } catch (\Throwable $e) {
                Log::error("Gagal kirim notifikasi kontrak expired: " . $e->getMessage());
            }
        }

        $namesStr = implode(', ', $sentNames);
        return redirect()->back()->with('success', "📩 Notifikasi kontrak expired '#{$contract->project_number}' berhasil dikirim ke email staf PIC: {$namesStr}.");
    }

    /**
     * Periksa semua kontrak yang expired dan kirimkan notifikasi ke email PIC Staf CPT
     */
    public function checkAllExpired()
    {
        $result = CheckExpiredCptContracts::processExpiredNotifications(null, true);

        if ($result['status'] === 'no_recipients') {
            return redirect()->back()->with('error', 'Tidak ada email staf PIC CPT yang terdaftar di sistem.');
        }

        return redirect()->back()->with('success', "✨ Selesai: {$result['sent_count']} notifikasi email kontrak expired berhasil diantrikan ke Staf PIC.");
    }
}

