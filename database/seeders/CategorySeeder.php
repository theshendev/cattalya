<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'النگو طلا', 'slug' => 'bangle'],
            ['name' => 'انگشتر طلا', 'slug' => 'ring'],
            ['name' => 'پابند طلا', 'slug' => 'anklet'],
            ['name' => 'آویز زنانه', 'slug' => 'pendant'],
            ['name' => 'گردنبند طلا', 'slug' => 'necklace'],
            ['name' => 'دستبند طلا', 'slug' => 'bracelet'],
            ['name' => 'گوشواره طلا', 'slug' => 'earring'],
        ];

        foreach ($categories as $i => $cat) {
            Category::create(array_merge($cat, ['sort_order' => $i + 1, 'is_active' => true]));
        }
    }
}
