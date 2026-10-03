<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'site_name' => 'کاتالیا',
            'site_name_en' => 'Cattalya',
            'site_description' => 'گالری کاتالیا در سال ۱۴۰۲ فعالیت خودش رو در حوزه فروش زیورآلات طلا شروع کرده.',
            'footer_copyright' => '© ۱۴۰۵ کاتالیا | تمامی حقوق مادی و معنوی این وب‌سایت محفوظ است.',
            'contact_phone' => '',
            'contact_email' => '',
            'instagram_url' => '',
            'whatsapp_number' => '',
            'free_shipping_threshold' => '12000000',
            'shipping_cost' => '0',
            'gift_packaging_cost' => '0',
        ];

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}
