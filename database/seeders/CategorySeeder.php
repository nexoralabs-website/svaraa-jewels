<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed default jewellery categories.
     * Safe to run multiple times — uses firstOrCreate to avoid duplicates.
     */
    public function run(): void
    {
        $categories = [
            'Earrings',
        ];

        foreach ($categories as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name'   => $name,
                    'status' => true,
                ]
            );
        }
    }
}
