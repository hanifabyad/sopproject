<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Library;
use App\Models\User;
use Illuminate\Support\Facades\File;

class ELibraryCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        // Ensure dummy.pdf exists in storage
        $dummyPath = storage_path('app/public/dummy.pdf');
        if (!File::exists($dummyPath)) {
            File::put($dummyPath, "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Count 1/Kids[3 0 R]>>endobj\n3 0 obj<</Type/Page/MediaBox[0 0 595 842]/Parent 2 0 R/Resources<<>>>>endobj\nxref\n0 4\n0000000000 65535 f \n0000000009 00000 n \n0000000052 00000 n \n0000000101 00000 n \ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n178\n%%EOF");
        }

        $filePath = 'dummy.pdf';

        // 1. BUSINESS UNITS CATALOG
        $buStructure = [
            'RETAIL' => [
                'company' => 'PT. LINTAS BINTAN SAMUDERA',
                'units' => [
                    'SPBU' => [
                        ['title' => 'Manual Mutu & Kebijakan Pelayanan SPBU', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Penerimaan, Uji Densitas, dan Pengisian Tangki Pendam SPBU', 'type' => 'sop'],
                        ['title' => 'SOP Prosedur Penurunan BBM Mobil Tangki ke Tangki Pendam', 'type' => 'sop'],
                        ['title' => 'Job Description & Uraian Jabatan Pengawas dan Operator SPBU', 'type' => 'jobdesk'],
                        ['title' => 'KPI & Target Kinerja Operasional SPBU', 'type' => 'kpi'],
                        ['title' => 'IK & Formulir Checklist Tera dan Kalibrasi Nozzle Dispenser', 'type' => 'ik-forms'],
                    ],
                    'LPG PSO' => [
                        ['title' => 'Manual Mutu Penyaluran Gas LPG Bersubsidi 3KG', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pengisian & Distribusi Tabung Gas LPG 3KG PSO', 'type' => 'sop'],
                        ['title' => 'SOP Uji Kebocoran & Retur Tabung LPG 3KG Rusak', 'type' => 'sop'],
                        ['title' => 'Job Description Kepala Gudang & Operator Pengisian LPG PSO', 'type' => 'jobdesk'],
                        ['title' => 'KPI Kepatuhan Distribusi & Kuota LPG PSO', 'type' => 'kpi'],
                        ['title' => 'IK & Form Inspeksi Visual Segel Valve Tabung LPG', 'type' => 'ik-forms'],
                    ],
                    'LPG NPSO' => [
                        ['title' => 'Kebijakan Mutu Niaga Gas Non-Subsidi (12KG & 50KG)', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Penjualan, Pengantaran, dan Handling Tabung LPG NPSO', 'type' => 'sop'],
                        ['title' => 'Job Description Sales & Koordinator Distribusi LPG Non-Subsidi', 'type' => 'jobdesk'],
                        ['title' => 'KPI Penjualan & Perputaran Tabung Gas NPSO', 'type' => 'kpi'],
                        ['title' => 'IK & Formulir Surat Jalan Pengiriman Tabung Gas NPSO', 'type' => 'ik-forms'],
                    ],
                    'PKSP' => [
                        ['title' => 'Peta Proses Bisnis & Kebijakan Mutu PT PKSP', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Layanan Jasa & Operasional Retail Terpadu PKSP', 'type' => 'sop'],
                        ['title' => 'Job Description Supervisor & Staf Lapangan PKSP', 'type' => 'jobdesk'],
                        ['title' => 'KPI Produktivitas Unit Kerja PKSP', 'type' => 'kpi'],
                        ['title' => 'Formulir Berita Acara Serah Terima Pekerjaan PKSP', 'type' => 'ik-forms'],
                    ],
                    'TRP' => [
                        ['title' => 'Kebijakan Mutu Manajemen Logistik Retail TRP', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pengelolaan Armada dan Rute Pengiriman Retail TRP', 'type' => 'sop'],
                        ['title' => 'Job Description Driver & Dispatcher Armada TRP', 'type' => 'jobdesk'],
                        ['title' => 'KPI Efisiensi Bahan Bakar & Ketepatan Pengantaran TRP', 'type' => 'kpi'],
                        ['title' => 'IK & Formulir Checklist Pra-Jalan Kendaraan Pengangkut TRP', 'type' => 'ik-forms'],
                    ],
                    'INMAR (CNGM)' => [
                        ['title' => 'Manual Mutu Operasional Maritim & Compressed Natural Gas', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pengisian, Transportasi, dan Pengamanan Tangki CNG Maritim', 'type' => 'sop'],
                        ['title' => 'SOP Tanggap Darurat Kebocoran Gas di Kapal & Dermaga', 'type' => 'sop'],
                        ['title' => 'Job Description Chief Engineer & Teknisi Gas INMAR', 'type' => 'jobdesk'],
                        ['title' => 'KPI Keamanan Operasional & Volume Penyaluran Gas INMAR', 'type' => 'kpi'],
                        ['title' => 'IK & Form Checklist Tekanan Tabung CNG Maritim', 'type' => 'ik-forms'],
                    ],
                ]
            ],
            'KOMERSIL' => [
                'company' => 'PT. CAHAYA PERDANA TRANSALAM',
                'units' => [
                    'CPT & MHM' => [
                        ['title' => 'Manual Mutu & Standar Pelayaran Niaga CPT & MHM', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Prosedur Bunker BBM Kapal & Pelayaran Niaga Laut', 'type' => 'sop'],
                        ['title' => 'SOP Perawatan Mesin Induk Kapal & Kelaiklautan', 'type' => 'sop'],
                        ['title' => 'Job Description Nakhoda, Mualim, dan Masinis Kapal CPT', 'type' => 'jobdesk'],
                        ['title' => 'KPI Utilisasi Armada Kapal & On-Time Performance', 'type' => 'kpi'],
                        ['title' => 'IK & Form Logbook Pelayaran & Pengisian BBM Bunker', 'type' => 'ik-forms'],
                    ],
                    'SBS' => [
                        ['title' => 'Peta Proses Bisnis Pengelolaan Shipyard & Drydock SBS', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Docking, Undocking, dan Perbaikan Lambung Kapal SBS', 'type' => 'sop'],
                        ['title' => 'Job Description Yard Manager & Welder Specialist SBS', 'type' => 'jobdesk'],
                        ['title' => 'KPI Durasi Repair Kapal & Kepuasan Pelanggan Docking', 'type' => 'kpi'],
                        ['title' => 'IK & Form Inspeksi Ketebalan Pelat Lambung Kapal', 'type' => 'ik-forms'],
                    ],
                    'GVI' => [
                        ['title' => 'Kebijakan Mutu Pemasaran Gas Industri Gas Venture Indonesia', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Distribusi Gas Industri & Manajemen Tekanan Jaringan GVI', 'type' => 'sop'],
                        ['title' => 'Job Description Sales Engineer & Operator Distribusi Gas GVI', 'type' => 'jobdesk'],
                        ['title' => 'KPI Penjualan Gas Industri & Keandalan Suplai GVI', 'type' => 'kpi'],
                        ['title' => 'IK & Form Kalibrasi Metering Gas Industri GVI', 'type' => 'ik-forms'],
                    ],
                ]
            ],
            'SCM' => [
                'company' => 'PT. PRIMA KARYA MANDIRI',
                'units' => [
                    'PROCUREMENT' => [
                        ['title' => 'Kebijakan Mutu Pengadaan Barang dan Jasa Terpusat SCM', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Evaluasi Vendor, Penerbitan PO, dan Negosiasi Pengadaan', 'type' => 'sop'],
                        ['title' => 'Job Description Procurement Specialist & Buyer', 'type' => 'jobdesk'],
                        ['title' => 'KPI Efisiensi Biaya Pengadaan & Lead Time Purchase Order', 'type' => 'kpi'],
                        ['title' => 'Formulir Purchase Requisition & Komparasi Penawaran Vendor', 'type' => 'ik-forms'],
                    ],
                    'WAREHOUSE' => [
                        ['title' => 'Manual Pengelolaan Gudang Pusat & Manajemen Persediaan', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Penerimaan Barang, Stock Opname, dan FIFO Inventory', 'type' => 'sop'],
                        ['title' => 'Job Description Kepala Gudang & Staf Inventory', 'type' => 'jobdesk'],
                        ['title' => 'KPI Akurasi Stock Opname & Inventory Turnover', 'type' => 'kpi'],
                        ['title' => 'IK & Form Berita Acara Penerimaan Barang Gudang', 'type' => 'ik-forms'],
                    ],
                    'ASET' => [
                        ['title' => 'Kebijakan Mutu Inventarisasi & Pemeliharaan Aset Perusahaan', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pencatatan, Pelabelan Barcode, dan Mutasi Aset Tetap', 'type' => 'sop'],
                        ['title' => 'Job Description Asset Management Officer', 'type' => 'jobdesk'],
                        ['title' => 'KPI Rasio Utilisasi Aset & Ketepatan Audit Fisik Aset', 'type' => 'kpi'],
                        ['title' => 'Formulir Berita Acara Serah Terima & Pemindahtanganan Aset', 'type' => 'ik-forms'],
                    ],
                    'GA' => [
                        ['title' => 'Standar Mutu Pelayanan Umum & Sarana Prasarana Gedung (GA)', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pemeliharaan Gedung, Kebersihan, dan Pengelolaan Kendaraan Dinas', 'type' => 'sop'],
                        ['title' => 'Job Description Supervisor General Affair & Maintenance', 'type' => 'jobdesk'],
                        ['title' => 'KPI Waktu Tanggap Perbaikan Fasilitas Kantor (SLA GA)', 'type' => 'kpi'],
                        ['title' => 'Formulir Permintaan Perbaikan Sarpras & Log Peminjaman Mobil Dinas', 'type' => 'ik-forms'],
                    ],
                ]
            ],
            'FA' => [
                'company' => 'PT. PRIMA KARYA MANDIRI',
                'units' => [
                    'KEUANGAN & ACCOUNTING' => [
                        ['title' => 'Kebijakan Akuntansi & Tata Kelola Keuangan Perusahaan', 'type' => 'dokumen-mutu'],
                        ['title' => 'SOP Pengajuan Pembayaran, Rekonsiliasi Bank, dan Tutup Buku Bulanan', 'type' => 'sop'],
                        ['title' => 'SOP Pengelolaan Kas Kecil (Petty Cash) & Verifikasi Bukti Transaksi', 'type' => 'sop'],
                        ['title' => 'Job Description Finance Manager & Senior Accountant', 'type' => 'jobdesk'],
                        ['title' => 'KPI Ketepatan Laporan Keuangan Bulanan & Likuiditas Kas', 'type' => 'kpi'],
                        ['title' => 'Formulir Payment Voucher & Pertanggungjawaban Kas Bon', 'type' => 'ik-forms'],
                    ],
                ]
            ],
        ];

        // 2. SUPPORT DEPARTMENTS CATALOG
        $supportStructure = [
            'HC' => [
                ['title' => 'Pedoman Mutu Manajemen SDM & Pengembangan Organisasi', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Rekrutmen, Seleksi, dan Onboarding Karyawan Baru', 'type' => 'sop'],
                ['title' => 'SOP Manajemen Penggajian, Lembur, dan Absensi Karyawan', 'type' => 'sop'],
                ['title' => 'Job Description Human Capital Specialist & HRBP', 'type' => 'jobdesk'],
                ['title' => 'KPI Tingkat Retensi Karyawan & Pelaksanaan Pelatihan SDM', 'type' => 'kpi'],
                ['title' => 'Formulir Pengajuan Cuti, Izin, dan Evaluasi Masa Percobaan', 'type' => 'ik-forms'],
            ],
            'IT' => [
                ['title' => 'Kebijakan Tata Kelola Teknologi Informasi & Keamanan Siber', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Manajemen Backup Data, Disaster Recovery, dan Server Security', 'type' => 'sop'],
                ['title' => 'SOP Penanganan Insiden TI (IT Helpdesk) & Pemeliharaan Jaringan', 'type' => 'sop'],
                ['title' => 'Job Description IT Manager, Systems Analyst, dan Network Engineer', 'type' => 'jobdesk'],
                ['title' => 'KPI Uptime Server Sistem e-QMS & Waktu Penyelesaian Tiket TI', 'type' => 'kpi'],
                ['title' => 'Formulir Permintaan Akses Akun Pengguna & Hak Akses Server', 'type' => 'ik-forms'],
            ],
            'HSE' => [
                ['title' => 'Manual Sistem Manajemen Keselamatan dan Kesehatan Kerja (SMK3)', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Tanggap Darurat Kebakaran, Gempa Bumi, dan Evakuasi Lapangan', 'type' => 'sop'],
                ['title' => 'SOP Investigasi Kecelakaan Kerja & Pelaporan Insiden Lingkungan', 'type' => 'sop'],
                ['title' => 'Job Description HSE Officer & Safety Inspector', 'type' => 'jobdesk'],
                ['title' => 'KPI Zero Accident Rate & Pelaksanaan Safety Drill Berkala', 'type' => 'kpi'],
                ['title' => 'Formulir Izin Kerja Berbahaya (Permit to Work / JSA)', 'type' => 'ik-forms'],
            ],
            'QMS' => [
                ['title' => 'Manual Mutu Terpadu ISO 9001:2015 PT PKM Group', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Pengendalian Dokumen Mutu, Catatan Rekaman, dan Distribusi e-QMS', 'type' => 'sop'],
                ['title' => 'SOP Pelaksanaan Audit Mutu Internal & Tinjauan Manajemen', 'type' => 'sop'],
                ['title' => 'Job Description QMS Lead Auditor & Document Controller', 'type' => 'jobdesk'],
                ['title' => 'KPI Kepatuhan Jadwal Audit Internal & Tindak Lanjut Temuan Mutu', 'type' => 'kpi'],
                ['title' => 'Formulir Permintaan Tindakan Korektif dan Preventif (CAPA)', 'type' => 'ik-forms'],
            ],
            'INTERNAL AUDIT' => [
                ['title' => 'Piagam Audit Internal & Pedoman Pengawasan Kepatuhan', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Perencanaan, Pelaksanaan, dan Pelaporan Audit Operasional', 'type' => 'sop'],
                ['title' => 'Job Description Senior Internal Auditor & Compliance Auditor', 'type' => 'jobdesk'],
                ['title' => 'KPI Tingkat Penyelesaian Audit Tahunan & Resolusi Temuan', 'type' => 'kpi'],
                ['title' => 'Formulir Lembar Kerja Audit & Rekomendasi Perbaikan Tata Kelola', 'type' => 'ik-forms'],
            ],
            'LOGISTIC' => [
                ['title' => 'Kebijakan Pengelolaan Rantai Pasok & Distribusi Produk', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Pengiriman Produk Cair & Manajemen Pengemudi Tangki', 'type' => 'sop'],
                ['title' => 'Job Description Logistics Supervisor & Route Planner', 'type' => 'jobdesk'],
                ['title' => 'KPI Tingkat Ketepatan Pengiriman (OTIF) & Efisiensi Rute', 'type' => 'kpi'],
                ['title' => 'Formulir Surat Perintah Jalan (SPJ) & Checklist Muatan Truk', 'type' => 'ik-forms'],
            ],
            'OPS' => [
                ['title' => 'Pedoman Mutu Manajemen Operasi Terpadu Wilayah', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Monitoring Operasional Lapangan & Koordinasi Lintas Unit', 'type' => 'sop'],
                ['title' => 'Job Description Operations Manager & Field Coordinator', 'type' => 'jobdesk'],
                ['title' => 'KPI Produktivitas Unit Lapangan & Efisiensi Biaya Operasional', 'type' => 'kpi'],
                ['title' => 'Formulir Laporan Harian Operasional (LHO) & Evaluasi Unit', 'type' => 'ik-forms'],
            ],
            'FINANCE' => [
                ['title' => 'Pedoman Manajemen Keuangan, Cash Flow, dan Perpajakan', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Pengajuan Reimbursement Operasional & Petty Cash Kantor', 'type' => 'sop'],
                ['title' => 'Job Description Finance Treasury & Tax Officer', 'type' => 'jobdesk'],
                ['title' => 'KPI Akurasi Proyeksi Arus Kas & Kepatuhan Pelaporan SPT Pajak', 'type' => 'kpi'],
                ['title' => 'Formulir Pengajuan Dana Proyek & Bukti Potong Pajak PPh', 'type' => 'ik-forms'],
            ],
            'LEGAL' => [
                ['title' => 'Pedoman Mutu Kepatuhan Hukum & Legal Drafting Kontrak', 'type' => 'dokumen-mutu'],
                ['title' => 'SOP Review Perjanjian Kerjasama (PKS), Kontrak Bisnis, dan Perizinan', 'type' => 'sop'],
                ['title' => 'Job Description Legal Counsel & Corporate Secretary Specialist', 'type' => 'jobdesk'],
                ['title' => 'KPI Waktu Penyelesaian Legal Opinion & Validitas Izin Usaha', 'type' => 'kpi'],
                ['title' => 'Formulir Permohonan Telaah Kontrak Hukum & Monitoring Masa Berlaku Izin', 'type' => 'ik-forms'],
            ],
        ];

        // Insert Business Unit Documents
        foreach ($buStructure as $divName => $divInfo) {
            $company = $divInfo['company'];
            foreach ($divInfo['units'] as $buName => $docs) {
                foreach ($docs as $doc) {
                    Library::updateOrCreate(
                        [
                            'title'         => $doc['title'],
                            'category'      => 'divisi',
                            'business_unit' => $buName,
                        ],
                        [
                            'division_name' => $divName,
                            'company_name'  => $company,
                            'support_type'  => null,
                            'file_path'     => $filePath,
                            'uploaded_by'   => $adminId,
                            'view_count'    => rand(5, 45),
                        ]
                    );
                }
            }
        }

        // Insert Support Department Documents
        foreach ($supportStructure as $deptName => $docs) {
            foreach ($docs as $doc) {
                Library::updateOrCreate(
                    [
                        'title'         => $doc['title'],
                        'category'      => 'support',
                        'business_unit' => $deptName,
                    ],
                    [
                        'division_name' => 'Support',
                        'company_name'  => 'PT. PRIMA KARYA MANDIRI',
                        'support_type'  => $deptName,
                        'file_path'     => $filePath,
                        'uploaded_by'   => $adminId,
                        'view_count'    => rand(8, 60),
                    ]
                );
            }
        }
    }
}
