<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RoleManagementService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncRolesFromExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eqms:sync-roles-excel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi daftar nama pejabat, email resmi, dan jabatan dinamis dari dokumen Excel Role Digital SOP 26.07.26';

    /**
     * Data master pejabat resmi hasil ekstraksi Excel Role Digital SOP 26.07.26.xlsx
     */
    private array $excelData = [
        [
            'full_name' => 'Ibnu Mirza',
            'username'  => 'ibnumirza',
            'email'     => 'ibnumirza@pkmgroup.co.id',
            'role'      => 'Ka. Div Retail',
        ],
        [
            'full_name' => 'Lalu Wandi',
            'username'  => 'laluwandi',
            'email'     => 'wandi@pkmgroup.co.id',
            'role'      => 'Wa. Ka. Div Retail',
        ],
        [
            'full_name' => 'Trinwetty',
            'username'  => 'trinwetty',
            'email'     => 'trinwetty@pkmgroup.co.id',
            'role'      => 'Chief of Staff',
        ],
        [
            'full_name' => 'Zikri',
            'username'  => 'zikri',
            'email'     => 'zikri@pkmgroup.co.id',
            'role'      => 'Direktur Utama',
        ],
        [
            'full_name' => 'Nazri Kudsi',
            'username'  => 'nazrikudsi',
            'email'     => 'nazri@pkmgroup.co.id',
            'role'      => 'Ka. BU SPBU',
        ],
        [
            'full_name' => 'Gario Anora Daya',
            'username'  => 'garioanora',
            'email'     => 'gario@pkmgroup.co.id',
            'role'      => 'Chief F&A SPBU',
        ],
        [
            'full_name' => 'Wendi Fadila Indri',
            'username'  => 'wendifadila',
            'email'     => 'wendi@pkmgroup.co.id',
            'role'      => 'Ka. Operasional SPBU',
        ],
        [
            'full_name' => 'Teddy',
            'username'  => 'teddy',
            'email'     => 'teddyvvoo@gmail.com',
            'role'      => 'Ka. Operasional BBM Retail',
        ],
        [
            'full_name' => 'Suhaimi',
            'username'  => 'suhaimi',
            'email'     => 'staff.audit@pkmgroup.co.id',
            'role'      => 'Dept. Internal Audit',
        ],
        [
            'full_name' => 'Lusman Gustaman',
            'username'  => 'lusman',
            'email'     => 'lusman.gustaman@pkmgroup.co.id',
            'role'      => 'Koordinator Sales & Marketing',
        ],
        [
            'full_name' => 'Tri Minarni',
            'username'  => 'triminami',
            'email'     => 'tri@pkmgroup.co.id',
            'role'      => 'Ka. BU Gas & SPBE',
        ],
        [
            'full_name' => 'Ekowati',
            'username'  => 'ekowati',
            'email'     => 'ekowati@pkmgroup.co.id',
            'role'      => 'Chief F&A Gas',
        ],
        [
            'full_name' => 'Sri Warsana',
            'username'  => 'sriwarsana',
            'email'     => 'ka-ops.pksp@pkmgroup.co.id',
            'role'      => 'Ka. Cab TBK',
        ],
        [
            'full_name' => 'Yusri',
            'username'  => 'yusri',
            'email'     => 'yusri@pkmgroup.co.id',
            'role'      => 'Ka. Cab Guntung',
        ],
        [
            'full_name' => 'Wendi Jatmiko',
            'username'  => 'wendijatmiko',
            'email'     => 'wendi.jatmiko@pkmretail.co.id',
            'role'      => 'Ka. Cab TPI',
        ],
        [
            'full_name' => 'Iman Helmi Ardani',
            'username'  => 'imanhelmi',
            'email'     => 'iman.helmi@pkmgroup.co.id',
            'role'      => 'Ka. Jakarta',
        ],
        [
            'full_name' => 'Jaka Pamungkas',
            'username'  => 'jakapamungkas',
            'email'     => 'ka-ops.gas@pkmgroup.co.id',
            'role'      => 'Ka. Operasional Batam',
        ],
        [
            'full_name' => 'Aditya Wisnu',
            'username'  => 'adityawisnu',
            'email'     => 'aditya@pkmgroup.co.id',
            'role'      => 'Ka. BU Inmarr',
        ],
        [
            'full_name' => 'Suseno Seno',
            'username'  => 'suseno',
            'email'     => 'suseno@pkmgroup.co.id',
            'role'      => 'Chief F & A Inmarr',
        ],
        [
            'full_name' => 'Erwan',
            'username'  => 'erwan',
            'email'     => 'ka-ops.cngm@pkmgroup.co.id',
            'role'      => 'Ka. Operasional Inmarr',
        ],
        [
            'full_name' => 'Rahmat Sidikhi',
            'username'  => 'rahmatsidikhi',
            'email'     => 'kasie.ga@pkmgroup.co.id',
            'role'      => 'KA DEPT. Legal & HC Manager',
        ],
        [
            'full_name' => 'Indrajat',
            'username'  => 'indrajat',
            'email'     => 'indrajat@pkmgroup.co.id',
            'role'      => 'Marine Superintendent',
        ],
        [
            'full_name' => 'Yayu Indah Maya',
            'username'  => 'yayuindahmaya',
            'email'     => 'yayu@pkmgroup.co.id',
            'role'      => 'Ka. BU CPT',
        ],
        [
            'full_name' => 'Handika',
            'username'  => 'handika',
            'email'     => 'handika@pkmgroup.co.id',
            'role'      => 'CPT Operasional',
        ],
        [
            'full_name' => 'Asrofi',
            'username'  => 'asrofi',
            'email'     => 'asrofi@pkmgroup.co.id',
            'role'      => 'CPT Maintenance',
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi peran dan akun dari Excel...');

        $created = 0;
        $updated = 0;
        $registeredRoles = 0;

        foreach ($this->excelData as $item) {
            $role = trim($item['role']);

            // 1. Pastikan role terdaftar ke master JSON dinamis
            if (RoleManagementService::registerRole($role)) {
                $registeredRoles++;
            }

            // 2. Cari akun user yang cocok (by email atau username atau full_name)
            $user = User::where('email', $item['email'])
                ->orWhere('username', $item['username'])
                ->orWhereRaw('LOWER(full_name) = ?', [strtolower($item['full_name'])])
                ->first();

            if ($user) {
                if ($user->role === 'admin') {
                    $this->warn("Melewati user admin: {$user->username}");
                    continue;
                }

                $user->update([
                    'full_name' => $item['full_name'],
                    'email'     => $item['email'],
                    'role'      => $role,
                    'status'    => 1,
                ]);

                $this->line("<info>[UPDATED]</info> {$user->full_name} ({$user->username}) -> Email: {$user->email} | Role: {$user->role}");
                $updated++;
            } else {
                $user = User::create([
                    'username'  => $item['username'],
                    'full_name' => $item['full_name'],
                    'email'     => $item['email'],
                    'password'  => Hash::make('password123'),
                    'role'      => $role,
                    'status'    => 1,
                ]);

                $this->line("<comment>[CREATED]</comment> {$user->full_name} ({$user->username}) -> Email: {$user->email} | Role: {$user->role}");
                $created++;
            }
        }

        $this->newLine();
        $this->info("Sinkronisasi Selesai!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Role Terdaftar Dinamis', count(RoleManagementService::getAllRoles())],
                ['Akun Diperbarui (Updated)', $updated],
                ['Akun Baru Dibuat (Created)', $created],
            ]
        );

        return 0;
    }
}
