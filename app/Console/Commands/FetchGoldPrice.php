<?php

namespace App\Console\Commands;

use App\Models\GoldPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchGoldPrice extends Command
{
    protected $signature = 'gold:fetch';

    protected $description = 'Fetch current 18k gold price per gram and store it';

    public function handle(): int
    {
        $apiKey = config('services.navasan.api_key');

        if ($apiKey) {
            return $this->fetchFromNavasan($apiKey);
        }

        return $this->fetchFromTgju();
    }

    private function fetchFromNavasan(string $apiKey): int
    {
        try {
            $response = Http::timeout(10)->get('https://api.navasan.tech/latest/', [
                'api_key' => $apiKey,
                'item' => 'geram18',
            ]);

            if (! $response->ok()) {
                $this->warn('Navasan API failed ('.$response->status().'), falling back to TGJU...');

                return $this->fetchFromTgju();
            }

            $price = $response->json('geram18.value');

            if (! $price) {
                $this->warn('No price in Navasan response, falling back to TGJU...');

                return $this->fetchFromTgju();
            }

            return $this->store((float) $price);
        } catch (\Exception $e) {
            Log::warning('FetchGoldPrice Navasan error: '.$e->getMessage());
            $this->warn('Navasan error, falling back to TGJU...');

            return $this->fetchFromTgju();
        }
    }

    private function fetchFromTgju(): int
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['User-Agent' => 'Mozilla/5.0'])
                ->get('https://call4.tgju.org/ajax.json');

            if (! $response->ok()) {
                $this->error('TGJU API returned: '.$response->status());

                return self::FAILURE;
            }

            $rawPrice = $response->json('current.geram18.p');

            if (! $rawPrice) {
                $this->error('geram18 price not found in TGJU response');

                return self::FAILURE;
            }

            // TGJU returns Rials with commas — convert to Tomans
            $priceInTomans = (float) str_replace(',', '', $rawPrice) / 10;

            return $this->store($priceInTomans);
        } catch (\Exception $e) {
            Log::error('FetchGoldPrice TGJU error: '.$e->getMessage());
            $this->error('Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }

    private function store(float $pricePerGram): int
    {
        GoldPrice::create([
            'price_per_gram' => $pricePerGram,
            'source' => 'api',
            'fetched_at' => now(),
        ]);

        $this->info('✓ Gold price updated: '.number_format($pricePerGram).' تومن/گرم');

        return self::SUCCESS;
    }
}
