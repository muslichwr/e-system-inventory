<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama jika ada
        // Menggunakan DB facade untuk mengakses tabel pivot
        DB::table('model_has_roles')->where('role_id', '>', 0)->delete();
        DB::table('role_has_permissions')->where('role_id', '>', 0)->delete();
        DB::table('permissions')->where('guard_name', 'web')->delete();
        DB::table('roles')->where('guard_name', 'web')->delete();
        
        // Buat roles
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $pegawaiGudangRole = Role::create(['name' => 'Pegawai Gudang', 'guard_name' => 'web']);
        $manajerRole = Role::create(['name' => 'Manajer', 'guard_name' => 'web']);
        
        // Buat user untuk Admin
        $adminUser = User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        // Buat user untuk Pegawai Gudang
        $pegawaiGudangUser = User::create([
            'name' => 'Pegawai Gudang',
            'email' => 'pegawai@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        // Buat user untuk Manajer
        $manajerUser = User::create([
            'name' => 'Manajer Toko',
            'email' => 'manajer@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        
        // Assign roles to users
        $adminUser->assignRole($adminRole);
        $pegawaiGudangUser->assignRole($pegawaiGudangRole);
        $manajerUser->assignRole($manajerRole);
        
        // Tambahkan beberapa user tambahan untuk tiap role
        // Admin tambahan
        $adminUser2 = User::create([
            'name' => 'Admin 2',
            'email' => 'admin2@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $adminUser2->assignRole($adminRole);
        
        // Pegawai Gudang tambahan
        $pegawaiGudangUser2 = User::create([
            'name' => 'Pegawai Gudang 2',
            'email' => 'pegawai2@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $pegawaiGudangUser2->assignRole($pegawaiGudangRole);
        
        // Manajer tambahan
        $manajerUser2 = User::create([
            'name' => 'Manajer 2',
            'email' => 'manajer2@toko.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
        $manajerUser2->assignRole($manajerRole);
        
        $this->command->info('Roles and users created successfully!');
    }
}
