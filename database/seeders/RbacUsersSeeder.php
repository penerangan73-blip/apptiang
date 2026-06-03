<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacUsersSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@pju.local'],
            [
                'name' => 'Superadmin PJU',
                'role' => 'superadmin',
                'phone' => '0274-000-001',
                'address' => 'Kantor Pengelola PJU Gunungkidul',
                'password' => Hash::make('password'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'surveyor@pju.local'],
            [
                'name' => 'Dimas Pratama',
                'role' => 'surveyor',
                'phone' => '0812-0000-0001',
                'address' => 'Kabupaten Gunungkidul',
                'password' => Hash::make('password'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'verifikator@pju.local'],
            [
                'name' => 'Verifikator PJU',
                'role' => 'verifikator',
                'phone' => '0812-0000-0003',
                'address' => 'Kabupaten Gunungkidul',
                'password' => Hash::make('password'),
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'user@pju.local'],
            [
                'name' => 'Viewer PJU',
                'role' => 'user',
                'phone' => '0812-0000-0002',
                'address' => 'Kabupaten Gunungkidul',
                'password' => Hash::make('password'),
            ],
        );
    }
}
