<?php

namespace Database\Seeders;

use App\Models\CptContract;
use App\Models\User;
use Illuminate\Database\Seeder;

class CptContractSeeder extends Seeder
{
    /**
     * Run the database seeds from Pak Imam's contract spreadsheet.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $adminId = $admin ? $admin->id : 1;

        $contracts = [
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Kontrak',
                'project_title'  => 'Jasa Transportir Angkutan BBM Laut untuk Layanan VHS PT Timah Tbk',
                'project_name'   => 'Timah',
                'project_number' => 'KTR-457/PL000010/2023-S0',
                'start_date'     => '2023-08-01',
                'end_date'       => '2024-12-31',
                'status'         => 'active',
                'notes'          => null,
                'document_link'  => 'https://drive.google.com/file/d/110fkrk1i4xP2r2VEHoggkQuB3iyOuqND/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Addendum',
                'project_title'  => 'Pengangkutan Jasa Trasnportir Bahan Bakar Minyak (BBM) PT PLN UP 3 Dumai',
                'project_name'   => 'PLN UP 3 Dumai',
                'project_number' => 'KTR-118/PL000010/2023-S0',
                'start_date'     => '2023-07-01',
                'end_date'       => '2023-12-31',
                'status'         => 'expired',
                'notes'          => 'Telah dilakukan negosiasi harga pada tgl 29 Juli 2024 dan saat ini masih menunggu informasi lanjutan terkait penerbitan kontrak',
                'document_link'  => 'https://drive.google.com/file/d/1xZ3PgK6olrheUL1sdfYyYVBWOgmixGf6/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Surat Perintah Memulai Pekerjaan (SPMP)',
                'project_title'  => 'Jasa Layanan Franco Angkutan BBM untuk Pangkalan Sarana Operasi Bea Cukai Tipe A Tanjung Balai Karimun',
                'project_name'   => 'Shoretonk - Bea Cukai',
                'project_number' => 'SPMP-002/PL100110/2024-S3',
                'start_date'     => '2024-01-01',
                'end_date'       => null,
                'status'         => 'still_not_yet',
                'notes'          => 'Telah disubmit PPHK yang di ttd pihak CPT ke team Patlog Batam pada tgl 15 Juli 2024, selanjutnya masih menunggu informasi lanjutan untuk penerbitan PPHK',
                'document_link'  => 'https://drive.google.com/file/d/1gYbUo-t23TUaPbYUE_dhGq-DbXJuUGEN1/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Surat Perintah Memulai Pekerjaan (SPMP)',
                'project_title'  => 'Jasa Layanan Franco Petronas Carigali Ketapang Ltd di Batu Ampar Anchorage dan Of Gresik',
                'project_name'   => 'Ship to Ship - Kapal Petronas',
                'project_number' => 'SPMP-004/PL100110/2024-S3',
                'start_date'     => '2024-01-01',
                'end_date'       => null,
                'status'         => 'still_not_yet',
                'notes'          => 'Menunggu draft PPHK untuk di ttd pihak CPT',
                'document_link'  => 'https://drive.google.com/file/d/13hUaGz06PJTIUO7yn_FRF2Z_x6BXW7V/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Surat Perintah Memulai Pekerjaan (SPMP)',
                'project_title'  => 'Jasa Angkutan BBM untuk PT PLN Nusantara Power PLTGU Belawan',
                'project_name'   => 'PLTGU Belawan',
                'project_number' => 'SPMP-005/PL000100/2024-S3',
                'start_date'     => '2024-02-04',
                'end_date'       => '2024-04-30',
                'status'         => 'completed',
                'notes'          => null,
                'document_link'  => 'https://drive.google.com/file/d/1PCFelE7HdabLyNdNS_D7a1JSi0953eBD/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Pengembalian Cargo',
                'project_title'  => 'Pengembalian Cargo Muntok tujuan Belinyu',
                'project_name'   => 'Pengembalian Cargo Muntok tujuan Belinyu',
                'project_number' => '-',
                'start_date'     => null,
                'end_date'       => null,
                'status'         => 'still_not_yet',
                'notes'          => 'Proses followup administrasi',
                'document_link'  => null,
                'created_by'     => $adminId,
            ],
            [
                'customer'       => 'PT Patra Logistik',
                'contract_type'  => 'Surat Perintah Memulai Pekerjaan (SPMP)',
                'project_title'  => 'Jasa Layanan Angkutan Emergency BBM BO PLN Nusantara Power PLTG MPP Suppa',
                'project_name'   => 'PLTG MPP Suppa, Sulawesi Selatan',
                'project_number' => 'SPMP-077/PL000100/2024-S3',
                'start_date'     => '2024-03-05',
                'end_date'       => '2024-05-31',
                'status'         => 'completed',
                'notes'          => null,
                'document_link'  => 'https://drive.google.com/file/d/1je-WG7fEABsqnQMw_sSgtYk6biEkxB/view?usp=drive_link',
                'created_by'     => $adminId,
            ],
        ];

        foreach ($contracts as $data) {
            CptContract::updateOrCreate(
                [
                    'project_name'   => $data['project_name'],
                    'project_number' => $data['project_number'],
                ],
                $data
            );
        }
    }
}
