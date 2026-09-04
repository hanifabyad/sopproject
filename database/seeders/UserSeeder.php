<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds for essential organization accounts.
     */
    public function run(): void
    {
        $defaultPassword = Hash::make('password123');

        $users = [
            [
                'username'  => 'admin',
                'full_name' => 'Administrator e-QMS',
                'email'     => 'admin@pkmgroup.com',
                'role'      => 'admin',
                'status'    => true,
            ],
            [
                'username'  => 'dirut',
                'full_name' => 'Hendra Wijaya, S.E., M.M.',
                'email'     => 'dirut@pkmgroup.com',
                'role'      => 'Direktur Utama',
                'status'    => true,
            ],
            [
                'username'  => 'kadiv_retail',
                'full_name' => 'Agus Setiawan, S.T.',
                'email'     => 'kadiv.retail@pkmgroup.com',
                'role'      => 'Ka. Div Retail',
                'status'    => true,
            ],
            [
                'username'  => 'chief_fa',
                'full_name' => 'Linda Permata, S.E., Ak.',
                'email'     => 'chief.fa@pkmgroup.com',
                'role'      => 'Chief F&A',
                'status'    => true,
            ],
            [
                'username'  => 'ka_qms',
                'full_name' => 'Trinwetty, S.Si.',
                'email'     => 'qms@pkmgroup.com',
                'role'      => 'KA.DEPT.QMS',
                'status'    => true,
            ],
            [
                'username'  => 'ka_it',
                'full_name' => 'Rian Pratama, S.Kom.',
                'email'     => 'it@pkmgroup.com',
                'role'      => 'KA.DEPT.IT',
                'status'    => true,
            ],
            [
                'username'  => 'ka_hc',
                'full_name' => 'Maya Kartika, S.Psi.',
                'email'     => 'hc@pkmgroup.com',
                'role'      => 'KA.DEPT.HC',
                'status'    => true,
            ],
            [
                'username'  => 'ka_hse',
                'full_name' => 'Bambang Sutrisno, S.K.M.',
                'email'     => 'hse@pkmgroup.com',
                'role'      => 'KA.DEPT.HSE',
                'status'    => true,
            ],
            [
                'username'  => 'ka_spbu',
                'full_name' => 'Dedi Supriyadi',
                'email'     => 'spbu@pkmgroup.com',
                'role'      => 'Ka. BU SPBU',
                'status'    => true,
            ],
            [
                'username'  => 'ka_gas',
                'full_name' => 'Tri Minami',
                'email'     => 'gas@pkmgroup.com',
                'role'      => 'Ka. BU Gas & SPBE',
                'status'    => true,
            ],
            [
                'username'  => 'ka_inmar',
                'full_name' => 'Hanif Abyad',
                'email'     => 'inmar@pkmgroup.com',
                'role'      => 'Ka. BU Inmarr',
                'status'    => true,
            ],
            [
                'username'  => 'ka_cpt',
                'full_name' => 'Hendro Santoso',
                'email'     => 'cpt@pkmgroup.com',
                'role'      => 'Ka. BU CPT',
                'status'    => true,
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['username' => $userData['username']],
                array_merge($userData, ['password' => $defaultPassword])
            );
        }
    }
}