<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@cattalya.ir'],
            [
                'name' => 'مدیر سایت',
                'password' => bcrypt('admin123'),
                'is_admin' => true,
            ]
        );
    }
}
