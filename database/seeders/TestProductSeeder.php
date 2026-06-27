<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class TestProductSeeder extends Seeder
{
    public function run(): void
    {
        if (Category::count() === 0) {
            (new CategorySeeder())->run();
        }

        $product = Product::create([
            'name' => 'Gold Earrings',
            'slug' => 'gold-earrings',
            'description' => 'Beautiful 14k gold earrings',
            'price' => 999.99,
            'stock' => 10,
            'status' => true,
            'category_id' => Category::where('slug', 'earrings')->firstOrFail()->id
        ]);

        $product->images()->create(['image' => 'placeholder.jpg']);
    }
}
