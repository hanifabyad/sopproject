<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\File;

class RoleManagementService
{
    /**
     * Path file JSON master role dinamis
     */
    public static function getJsonPath(): string
    {
        return storage_path('app/company_roles.json');
    }

    /**
     * Default fallback roles jika file JSON belum ada
     */
    public static function getDefaultRoles(): array
    {
        return [
            // --- PIMPINAN & EKSEKUTIF ---
            'Direktur Utama',
            'Direktur CPT',
            'Chief of Staff',
            'Ka. Div Retail',
            'Wa. Ka. Div Retail',
            'Ka. Div. Komersial',
            'Ka. Div F&A',

            // --- DEPARTEMEN SUPPORT ---
            'Ka. Dept. QMS',
            'Ka. Dept. HC',
            'Legal & HC Manager',
            'Ka. Dept. Admin & Legal',
            'Ka. Dept. IT',
            'Ka. Dept. HSE',
            'Ka. Dept. Internal Audit',
            'Dept. Internal Audit',
            'Ka. Dept. Internal Audit & Risk Management',
            'Ka. Dept. Corporate Seketaris',
            'Ka. Dept. Procurement',
            'Ka. Dept. F & A',
            'Ka. Dept. Keuangan',
            'Ka. Dept. Pajak',
            'Ka. Dept. Sales & Marketing',

            // --- DIVISI FINANCE & ACCOUNTING ---
            'Chief F&A Holding',

            // --- DIVISI RETAIL & KOMERSIAL ---
            'Koordinator Sales & Marketing',

            // --- UNIT BISNIS (BU): SPBU & RETAIL ---
            'Ka. BU SPBU',
            'Chief F&A SPBU',
            'Ka. Operasional SPBU',
            'Ka. Operasional BBM Retail',

            // --- UNIT BISNIS (BU): GAS & SPPBE ---
            'Ka. BU Gas & SPBE',
            'Ka. BU Gas & SPPBE',
            'Chief F&A Gas',
            'Ka. Operasional Batam',
            'Ka. Operasional TBK',
            'Ka. Operasional SPPBE',
            'Ka. Cab TBK',
            'Ka. Cab Guntung',
            'Ka. Cab TPI',
            'Ka. Jakarta',

            // --- UNIT BISNIS (BU): INMARR ---
            'Ka. BU Inmarr',
            'Chief F & A Inmarr',
            'Ka. Operasional Inmarr',

            // --- UNIT BISNIS (BU): CPT ---
            'Ka. BU CPT',
            'Chief BU CPT',
            'Marine Superintendent',
            'Management Representative',
            'Ka. Operasional CPT',
            'Ka. Departemen Teknik',
            'Service Delivery Manager',
            'Ka. QQ',
            'Sales Manager',

            // --- UNIT BISNIS (BU): SBS ---
            'Ka. BU SBS',
            'Chief F&A SBS',
            'Kepala Produksi',

            // --- LAINNYA ---
            'Karyawan QMS',
            'Office',
            'Reviewer'
        ];
    }

    /**
     * Ambil seluruh daftar role dinamis (JSON + DB users)
     */
    public static function getAllRoles(): array
    {
        $path = self::getJsonPath();
        $fileRoles = [];

        if (File::exists($path)) {
            $content = File::get($path);
            $decoded = json_decode($content, true);
            if (is_array($decoded)) {
                $fileRoles = $decoded;
            }
        } else {
            $fileRoles = self::getDefaultRoles();
            self::saveRolesToJson($fileRoles);
        }

        // Ambil juga role unik dari tabel users jika ada role kustom yang tersimpan di DB
        try {
            $dbRoles = User::whereNotNull('role')
                ->where('role', '!=', '')
                ->where('role', '!=', 'admin')
                ->distinct()
                ->pluck('role')
                ->toArray();
        } catch (\Throwable $e) {
            $dbRoles = [];
        }

        // Gabungkan secara cerdas tanpa duplikasi (case-insensitive deduplication)
        $merged = [];
        $lowerMap = [];

        foreach (array_merge($fileRoles, $dbRoles) as $r) {
            $rClean = trim((string)$r);
            if ($rClean === '' || strtolower($rClean) === 'admin') continue;

            $low = strtolower($rClean);
            if (!isset($lowerMap[$low])) {
                $lowerMap[$low] = true;
                $merged[] = $rClean;
            }
        }

        return $merged;
    }

    /**
     * Ambil daftar role yang sudah dikelompokkan berdasarkan kategori unit kerja & departemen
     */
    public static function getCategorizedRoles(): array
    {
        $allRoles = self::getAllRoles();

        $categories = [
            'Direksi & Pimpinan Eksekutif' => [
                'Direktur Utama',
                'Direktur CPT',
                'Chief of Staff',
                'Ka. Div Retail',
                'Wa. Ka. Div Retail',
                'Ka. Div. Komersial',
                'Ka. Div F&A',
            ],
            'Departemen Support' => [
                'Ka. Dept. QMS',
                'Ka. Dept. HC',
                'Legal & HC Manager',
                'Ka. Dept. Admin & Legal',
                'Ka. Dept. IT',
                'Ka. Dept. HSE',
                'Ka. Dept. Internal Audit',
                'Dept. Internal Audit',
                'Ka. Dept. Internal Audit & Risk Management',
                'Ka. Dept. Corporate Seketaris',
                'Ka. Dept. Procurement',
                'Ka. Dept. F & A',
                'Ka. Dept. Keuangan',
                'Ka. Dept. Pajak',
                'Ka. Dept. Sales & Marketing',
            ],
            'Divisi Finance & Accounting' => [
                'Chief F&A Holding',
            ],
            'Divisi Retail & Komersial' => [
                'Koordinator Sales & Marketing',
            ],
            'Unit Bisnis: SPBU & Retail BBM' => [
                'Ka. BU SPBU',
                'Chief F&A SPBU',
                'Ka. Operasional SPBU',
                'Ka. Operasional BBM Retail',
            ],
            'Unit Bisnis: Gas & SPPBE' => [
                'Ka. BU Gas & SPBE',
                'Ka. BU Gas & SPPBE',
                'Chief F&A Gas',
                'Ka. Operasional Batam',
                'Ka. Operasional TBK',
                'Ka. Operasional SPPBE',
                'Ka. Cab TBK',
                'Ka. Cab Guntung',
                'Ka. Cab TPI',
                'Ka. Jakarta',
            ],
            'Unit Bisnis: Inmarr / CNGM' => [
                'Ka. BU Inmarr',
                'Chief F & A Inmarr',
                'Ka. Operasional Inmarr',
            ],
            'Unit Bisnis: CPT (Pelayaran & Maritim)' => [
                'Ka. BU CPT',
                'Chief BU CPT',
                'Marine Superintendent',
                'Management Representative',
                'Ka. Operasional CPT',
                'Ka. Departemen Teknik',
                'Service Delivery Manager',
                'Ka. QQ',
                'Sales Manager',
            ],
            'Unit Bisnis: SBS' => [
                'Ka. BU SBS',
                'Chief F&A SBS',
                'Kepala Produksi',
            ],
        ];

        $result = [];
        $assigned = [];

        foreach ($categories as $categoryName => $predefinedList) {
            $result[$categoryName] = [];
            foreach ($predefinedList as $item) {
                foreach ($allRoles as $r) {
                    if (strcasecmp($r, $item) === 0) {
                        $result[$categoryName][] = $r;
                        $assigned[strtolower($r)] = true;
                        break;
                    }
                }
            }
        }

        // Kelompokkan role kustom / tambahan ke kategori yang paling cocok
        $customGroup = [];
        foreach ($allRoles as $r) {
            $low = strtolower(trim($r));
            if (!isset($assigned[$low])) {
                if (str_contains($low, 'sbs')) {
                    $result['Unit Bisnis: SBS'][] = $r;
                } elseif (str_contains($low, 'spbu') || str_contains($low, 'bbm')) {
                    $result['Unit Bisnis: SPBU & Retail BBM'][] = $r;
                } elseif (str_contains($low, 'gas') || str_contains($low, 'lpg') || str_contains($low, 'spbe') || str_contains($low, 'sppbe')) {
                    $result['Unit Bisnis: Gas & SPPBE'][] = $r;
                } elseif (str_contains($low, 'inmar') || str_contains($low, 'cng')) {
                    $result['Unit Bisnis: Inmarr / CNGM'][] = $r;
                } elseif (str_contains($low, 'cpt') || str_contains($low, 'marine')) {
                    $result['Unit Bisnis: CPT (Pelayaran & Maritim)'][] = $r;
                } elseif (str_contains($low, 'dept') || str_contains($low, 'audit') || str_contains($low, 'legal') || str_contains($low, 'it') || str_contains($low, 'hc') || str_contains($low, 'qms') || str_contains($low, 'hse') || str_contains($low, 'support')) {
                    $result['Departemen Support'][] = $r;
                } elseif (str_contains($low, 'direktur') || str_contains($low, 'chief') || str_contains($low, 'pimpinan') || str_contains($low, 'kadiv') || str_contains($low, 'ka. div')) {
                    $result['Direksi & Pimpinan Eksekutif'][] = $r;
                } else {
                    $customGroup[] = $r;
                }
                $assigned[$low] = true;
            }
        }

        if (!empty($customGroup)) {
            $result['Jabatan Kustom / Lainnya'] = $customGroup;
        }

        return array_filter($result, fn($list) => !empty($list));
    }

    /**
     * Daftarkan jabatan baru ke dalam master JSON
     */
    public static function registerRole(string $role): bool
    {
        $cleanRole = trim($role);
        if ($cleanRole === '' || strtolower($cleanRole) === 'admin') {
            return false;
        }

        $all = self::getAllRoles();
        $exists = false;
        foreach ($all as $existing) {
            if (strcasecmp($existing, $cleanRole) === 0) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $all[] = $cleanRole;
            return self::saveRolesToJson($all);
        }

        return true;
    }

    /**
     * Hapus jabatan dari file master JSON
     */
    public static function deleteRole(string $role): bool
    {
        $cleanRole = trim($role);
        if ($cleanRole === '' || strtolower($cleanRole) === 'admin') {
            return false;
        }

        $all = self::getAllRoles();
        $filtered = array_values(array_filter($all, function ($existing) use ($cleanRole) {
            return strcasecmp(trim($existing), $cleanRole) !== 0;
        }));

        return self::saveRolesToJson($filtered);
    }

    /**
     * Ambil jumlah user aktif yang menggunakan masing-masing role
     */
    public static function getRoleUserCounts(): array
    {
        try {
            $raw = User::groupBy('role')
                ->selectRaw('role, count(*) as count')
                ->pluck('count', 'role')
                ->toArray();

            // Sediakan mapping case-insensitive
            $counts = [];
            foreach ($raw as $role => $count) {
                $counts[strtolower(trim((string)$role))] = (int)$count;
            }
            return $counts;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Simpan daftar role ke file JSON
     */
    public static function saveRolesToJson(array $roles): bool
    {
        $path = self::getJsonPath();
        $dir = dirname($path);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        // Urutkan nilai unik
        $unique = array_values(array_unique(array_filter(array_map('trim', $roles))));
        $json = json_encode($unique, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return File::put($path, $json) !== false;
    }

    /**
     * Helper pemetaan departemen terkait berdasarkan role/jabatan
     */
    public static function getDepartmentsForRole(?string $role): array
    {
        if (!$role) return [];
        $r = trim($role);

        $exactMap = [
            // Departemen Support
            'Ka. Dept. HC'                             => ['HC'],
            'KA.DEPT.HC'                               => ['HC'],
            'Ka. Dept. IT'                             => ['IT'],
            'KA.DEPT.IT'                               => ['IT'],
            'Ka. Dept. QMS'                            => ['QMS'],
            'KA.DEPT.QMS'                              => ['QMS'],
            'Management Representative'                => ['QMS', 'CPT & MHM', 'SBS', 'GVI'],
            'Ka. Dept. HSE'                            => ['HSE'],
            'KA.DEPT.HSE'                              => ['HSE'],
            'Ka. Dept. Admin & Legal'                  => ['LEGAL', 'ADMIN & LEGAL'],
            'KA.DEPT.ADMIN & LEGAL'                    => ['LEGAL', 'ADMIN & LEGAL'],
            'Legal & HC Manager'                       => ['HC', 'LEGAL', 'ADMIN & LEGAL'],
            'Legal & Human Capital Manager'            => ['HC', 'LEGAL', 'ADMIN & LEGAL'],
            'KA DEPT. Legal & HC Manager'              => ['HC', 'LEGAL', 'ADMIN & LEGAL'],
            'KA.DEPT.LEGAL & HC MANAGER'              => ['HC', 'LEGAL', 'ADMIN & LEGAL'],
            'HC Manager'                               => ['HC'],
            'Ka. Dept. Internal Audit'                 => ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'],
            'KA.DEPT.INTERNAL AUDIT'                   => ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'],
            'Dept. Internal Audit'                     => ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'],
            'Ka. Dept. Internal Audit & Risk Management' => ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'],
            'KA.DEPT.INTERNAL AUDIT & RISK MANAGEMENT' => ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'],
            'Ka. Dept. F & A'                          => ['FINANCE', 'KEUANGAN', 'F & A'],
            'KA.DEPT.F & A'                            => ['FINANCE', 'KEUANGAN', 'F & A'],
            'Ka. Dept. Keuangan'                       => ['FINANCE', 'KEUANGAN', 'F & A'],
            'KA.DEPT.KEUANGAN'                         => ['FINANCE', 'KEUANGAN', 'F & A'],
            'Ka. Dept. Pajak'                          => ['FINANCE', 'KEUANGAN', 'F & A'],
            'KA.DEPT.PAJAK'                            => ['FINANCE', 'KEUANGAN', 'F & A'],
            'Ka. Dept. Sales & Marketing'              => ['LOGISTIC', 'OPS'],
            'KA.DEPT.SALES & MARKETING'                => ['LOGISTIC', 'OPS'],
            'Koordinator Sales & Marketing'            => ['LOGISTIC', 'OPS'],
            'Ka. Dept. Procurement'                    => ['PROCUREMENT'],
            'KA.DEPT.PROCRUTMEN'                       => ['PROCUREMENT'],
            'Ka. Dept. Corporate Seketaris'            => ['WAREHOUSE', 'ASET', 'GA'],
            'KA.DEPT.CORPORATE SEKTARIS'               => ['WAREHOUSE', 'ASET', 'GA'],

            // Direksi & Divisi
            'Chief of Staff'                           => ['WAREHOUSE', 'ASET', 'GA', 'HC', 'IT', 'QMS', 'HSE'],
            'Chief F&A Holding'                        => ['FINANCE', 'KEUANGAN & ACCOUNTING', 'KEUANGAN', 'F & A'],
            'Chief F&A'                                => ['FINANCE', 'KEUANGAN & ACCOUNTING', 'KEUANGAN', 'F & A'],
            'Ka. Div F&A'                              => ['FINANCE', 'KEUANGAN & ACCOUNTING', 'KEUANGAN', 'F & A'],
            'Ka. Div Retail'                           => ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'LOGISTIC', 'OPS'],
            'Wa. Ka. Div Retail'                       => ['SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'LOGISTIC', 'OPS'],
            'Ka. Div. Komersial'                       => ['CPT & MHM', 'SBS', 'GVI', 'LOGISTIC', 'OPS'],
            'Direktur Utama'                           => ['HC', 'IT', 'QMS', 'HSE', 'LEGAL', 'INTERNAL AUDIT', 'FINANCE', 'LOGISTIC', 'OPS', 'SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)', 'CPT & MHM', 'SBS', 'GVI', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'],
            'Direktur CPT'                             => ['CPT & MHM', 'SBS', 'GVI'],

            // Marine & CPT
            'Marine Superintendent'                    => ['CPT & MHM', 'SBS', 'GVI', 'OPS'],
            'Ka. BU CPT'                               => ['CPT & MHM', 'SBS', 'GVI'],
            'Chief BU CPT'                             => ['CPT & MHM', 'SBS', 'GVI'],
            'Ka. Operasional CPT'                      => ['CPT & MHM', 'SBS', 'GVI', 'OPS'],
            'CPT Operasional'                          => ['CPT & MHM', 'SBS', 'GVI', 'OPS'],
            'Ka. Departemen Teknik'                    => ['CPT & MHM', 'SBS', 'GVI'],
            'Service Delivery Manager'                 => ['CPT & MHM', 'SBS', 'GVI', 'OPS'],
            'Ka. QQ'                                   => ['CPT & MHM', 'SBS', 'GVI', 'QMS'],
            'Sales Manager'                            => ['CPT & MHM', 'SBS', 'GVI', 'OPS'],

            // SBS
            'Ka. BU SBS'                               => ['SBS', 'CPT & MHM', 'GVI'],
            'Chief F&A SBS'                            => ['SBS', 'CPT & MHM', 'GVI', 'FINANCE', 'KEUANGAN'],
            'Kepala Produksi'                          => ['SBS', 'CPT & MHM', 'GVI', 'OPS'],

            // BU SPBU & Retail
            'Ka. BU SPBU'                              => ['SPBU'],
            'Chief F&A SPBU'                           => ['SPBU', 'FINANCE', 'KEUANGAN'],
            'Ka. Operasional SPBU'                     => ['SPBU'],
            'Ka. Operasional BBM Retail'               => ['SPBU'],

            // BU Gas & SPPBE
            'Ka. BU Gas & SPBE'                        => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. BU Gas & SPPBE'                       => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. BU GAS & SPPBE'                       => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Chief F&A Gas'                            => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'FINANCE', 'KEUANGAN'],
            'Ka. Operasional Batam'                    => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Operasional TBK'                      => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Operasional SPPBE'                    => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Cab TBK'                              => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Cab Guntung'                          => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Cab TPI'                              => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],
            'Ka. Jakarta'                              => ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'],

            // BU Inmarr
            'Ka. BU Inmarr'                            => ['INMAR (CNGM)'],
            'Chief F & A Inmarr'                       => ['INMAR (CNGM)', 'FINANCE', 'KEUANGAN'],
            'Ka. Operasional Inmarr'                   => ['INMAR (CNGM)'],
        ];

        // Case-insensitive exact search
        foreach ($exactMap as $key => $depts) {
            if (strcasecmp($key, $r) === 0) {
                return $depts;
            }
        }

        // Cerdas berbasis kata kunci (untuk peran kustom atau jabatan baru)
        $lower = strtolower($r);
        if (str_contains($lower, 'dirut') || str_contains($lower, 'direktur utama')) {
            return ['HC', 'IT', 'QMS', 'HSE', 'LEGAL', 'INTERNAL AUDIT', 'FINANCE', 'LOGISTIC', 'OPS', 'SPBU', 'LPG PSO', 'LPG NPSO', 'PKSP', 'TRP', 'INMAR (CNGM)', 'CPT & MHM', 'SBS', 'GVI', 'PROCUREMENT', 'WAREHOUSE', 'ASET', 'GA'];
        }
        if (str_contains($lower, 'cpt') || str_contains($lower, 'sbs') || str_contains($lower, 'gvi') || str_contains($lower, 'marine')) {
            return ['CPT & MHM', 'SBS', 'GVI'];
        }
        if (str_contains($lower, 'inmar') || str_contains($lower, 'cng')) {
            return ['INMAR (CNGM)'];
        }
        if (str_contains($lower, 'spbu') || str_contains($lower, 'retail') || str_contains($lower, 'bbm')) {
            return ['SPBU'];
        }
        if (str_contains($lower, 'gas') || str_contains($lower, 'lpg') || str_contains($lower, 'spbe') || str_contains($lower, 'sppbe')) {
            return ['LPG PSO', 'LPG NPSO', 'PKSP', 'TRP'];
        }
        if (str_contains($lower, 'hc') || str_contains($lower, 'human') || str_contains($lower, 'hr')) {
            return ['HC'];
        }
        if (str_contains($lower, 'it') || str_contains($lower, 'programmer') || str_contains($lower, 'edp')) {
            return ['IT'];
        }
        if (str_contains($lower, 'qms') || str_contains($lower, 'qa') || str_contains($lower, 'quality')) {
            return ['QMS'];
        }
        if (str_contains($lower, 'hse') || str_contains($lower, 'k3') || str_contains($lower, 'safety')) {
            return ['HSE'];
        }
        if (str_contains($lower, 'legal') || str_contains($lower, 'hukum')) {
            return ['LEGAL', 'ADMIN & LEGAL'];
        }
        if (str_contains($lower, 'audit')) {
            return ['INTERNAL AUDIT', 'INTERNAL AUDIT & RISK MANAGEMENT'];
        }
        if (str_contains($lower, 'f&a') || str_contains($lower, 'finance') || str_contains($lower, 'keuangan') || str_contains($lower, 'pajak') || str_contains($lower, 'accounting') || str_contains($lower, 'akuntansi')) {
            return ['FINANCE', 'KEUANGAN', 'F & A'];
        }
        if (str_contains($lower, 'sales') || str_contains($lower, 'market') || str_contains($lower, 'logistik') || str_contains($lower, 'logistic')) {
            return ['LOGISTIC', 'OPS'];
        }
        if (str_contains($lower, 'procure') || str_contains($lower, 'pengadaan')) {
            return ['PROCUREMENT'];
        }
        if (str_contains($lower, 'warehouse') || str_contains($lower, 'gudang') || str_contains($lower, 'aset') || str_contains($lower, 'ga')) {
            return ['WAREHOUSE', 'ASET', 'GA'];
        }

        return [];
    }
}
