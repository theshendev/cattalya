<?php

namespace Database\Seeders;

use App\Models\GoldPrice;
use Illuminate\Database\Seeder;

class GoldPriceSeeder extends Seeder
{
    public function run(): void
    {
        GoldPrice::create([
            'price_per_gram' => 18465000,
            'source' => 'manual',
            'fetched_at' => now(),
        ]);
    }
}
