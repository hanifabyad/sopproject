<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LibraryFolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $folders = [
            'Al-Quran',
            'Formulir',
            'Gallery',
            'Instruksi Kerja',
            'Internal MEMO',
            'Job Description',
            'Kebijakan',
            'Kebijakan Keuangan',
            'Logo',
            'Manual Mutu',
            'Materi Training',
            'Memorandum',
            'Other',
            'Prosedur',
            'Struktur Organisasi',
            'Surat Keputusan (SK)'
        ];

        foreach ($folders as $folder) {
            \App\Models\LibraryFolder::firstOrCreate([
                'name' => $folder,
                'parent_id' => null
            ]);
        }
    }
}
