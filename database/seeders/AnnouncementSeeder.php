<?php

namespace Database\Seeders;

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        Announcement::create([
            'text' => 'تمامی محصولات باتوجه به قیمت روز طلا قیمت گذاری شده اند.',
            'is_active' => true,
        ]);
    }
}
