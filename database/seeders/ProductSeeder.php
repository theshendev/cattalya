<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $earring = Category::where('slug', 'earring')->first();
        $necklace = Category::where('slug', 'necklace')->first();

        $products = [
            [
                'category_id' => $earring->id,
                'name' => 'گوشواره آویز طلا ۱۸ عیار زنانه مدل پروانه فیوژن',
                'slug' => 'earring-butterfly-fusion',
                'description' => 'گوشواره طلا ۱۸ عیار با طراحی پروانه فیوژن',
                'weight_grams' => 2.5,
                'wage_percent' => 7,
                'tax_percent' => 9,
                'fee' => 0,
                'stock' => 10,
            ],
            [
                'category_id' => $necklace->id,
                'name' => 'گردنبند طلا ۱۸ عیار زنانه مدل ستاره',
                'slug' => 'necklace-star',
                'description' => 'گردنبند طلا ۱۸ عیار با طراحی ستاره',
                'weight_grams' => 4.2,
                'wage_percent' => 8,
                'tax_percent' => 9,
                'fee' => 0,
                'stock' => 5,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
