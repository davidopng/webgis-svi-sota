<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 4 Akun Pengguna Terotentikasi:
     * 1. Admin
     * 2. Operator Surveyor 1
     * 3. Operator Surveyor 2
     * 4. Kepala Kampung
     */
    public function run(): void
    {
        // 1. Admin (Jhoni Arafat - Kasi Pemerintahan)
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Jhoni Arafat',
                'password' => Hash::make('AdminSota#2026!'),
                'role' => 'admin',
            ]
        );

        // 2. Operator Surveyor 1 (Harpiyansa)
        User::updateOrCreate(
            ['username' => 'operator1'],
            [
                'name' => 'Harpiyansa',
                'password' => Hash::make('Surveyor1@Sota26'),
                'role' => 'operator',
            ]
        );

        // 3. Operator Surveyor 2 (Kusnaeni - Kasi Pelayanan)
        User::updateOrCreate(
            ['username' => 'operator2'],
            [
                'name' => 'Kusnaeni',
                'password' => Hash::make('Surveyor2@Sota26'),
                'role' => 'operator',
            ]
        );

        // 4. Kepala Kampung (Conrada Murnan)
        User::updateOrCreate(
            ['username' => 'kepala_kampung'],
            [
                'name' => 'Conrada Murnan',
                'password' => Hash::make('KepalaSota#2026!'),
                'role' => 'kepala_kampung',
            ]
        );
    }
}
