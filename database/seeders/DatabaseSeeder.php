<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * Hanya menyediakan akun akses login (Admin 1, Admin 2, Warga 1) tanpa data dummy pengajuan.
     */
    public function run(): void
    {
        // 1. Admin 1 Kalurahan Purwobinangun
        User::updateOrCreate(
            ['email' => 'purwo34041601@gmail.com'],
            [
                'name' => 'Petugas Pelayanan Kalurahan 1',
                'email' => 'purwo34041601@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '081234567890',
                'verified_at' => now(),
                'verified_by' => 'Sistem',
            ]
        );

        // 2. Admin 2 Kalurahan Purwobinangun
        User::updateOrCreate(
            ['email' => 'ardyfadhil@gmail.com'],
            [
                'name' => 'Ardy Fadhil (Petugas 2)',
                'email' => 'ardyfadhil@gmail.com',
                'password' => Hash::make('Password123'),
                'role' => 'admin',
                'status' => 'active',
                'phone' => '081234567891',
                'verified_at' => now(),
                'verified_by' => 'Sistem',
            ]
        );

        // 3. Warga 1 (Aktif, siap login)
        User::updateOrCreate(
            ['nik' => '3404051205900001'],
            [
                'name' => 'Bambang Nugroho',
                'role' => 'warga',
                'nik' => '3404051205900001',
                'family_card_no' => '3404050101900001',
                'family_relationship' => 'Kepala Keluarga',
                'birth_place' => 'Sleman',
                'birth_date' => '1990-05-12',
                'gender' => 'L',
                'address' => 'Kadilobo, Purwobinangun, Pakem, Sleman',
                'rt' => '02',
                'rw' => '05',
                'phone' => '081234567892',
                'email' => 'warga1@purwobinangun.desa.id',
                'password' => Hash::make('password123'),
                'status' => 'active',
                'verified_at' => now(),
                'verified_by' => 'Petugas Pelayanan Kalurahan',
            ]
        );
    }
}
