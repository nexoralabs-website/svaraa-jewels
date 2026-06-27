<?php

namespace App\Services;

class ProductDescriptionService
{
    /**
     * Category-keyed template sets.
     * Earrings-only store — only the earrings and default templates are used.
     */
    private const TEMPLATES = [
        'earrings' => [
            'openers' => [
                'Elevate your elegance with our handcrafted {name},',
                'Adorn yourself with the graceful beauty of our {name},',
                'Make a statement with our exquisite {name},',
                'Celebrate every occasion with our stunning {name},',
            ],
            'middles' => [
                'designed with intricate detailing and premium craftsmanship.',
                'crafted to perfection with delicate artistry and fine materials.',
                'featuring a timeless design that blends tradition with modern grace.',
                'showcasing fine filigree work and lustrous finishes.',
            ],
            'closers' => [
                'Perfect for weddings, festive celebrations, and everyday luxury.',
                'Ideal for gifting or treating yourself to something truly special.',
                'A must-have addition to your jewellery collection for any festive occasion.',
                'Designed to complement both traditional and contemporary outfits with ease.',
            ],
        ],

        'default' => [
            'openers' => [
                'Discover unparalleled craftsmanship with our exquisite {name},',
                'Indulge in luxury with our beautifully crafted {name},',
                'Add a touch of brilliance to your collection with our {name},',
                'Experience the art of fine jewellery with our stunning {name},',
            ],
            'middles' => [
                'crafted with the finest materials and meticulous attention to detail.',
                'a testament to superior jewellery-making tradition and modern design.',
                'designed to be treasured for a lifetime and beyond.',
                'reflecting the pinnacle of luxury jewellery craftsmanship.',
            ],
            'closers' => [
                'A perfect gift for loved ones or a well-deserved treat for yourself.',
                'Elevate any outfit and make every moment memorable.',
                'Part of the Svaraa Jewels signature collection — luxury redefined.',
                'Timeless, elegant, and made to be cherished.',
            ],
        ],
    ];

    /**
     * Generate a premium product description from the product name and category.
     *
     * @param  string  $name      Product name, e.g. "Ruby Jhumka"
     * @param  string  $category  Category name, e.g. "Earrings"
     * @return string
     */
    public function generate(string $name, string $category): string
    {
        $key = strtolower(trim($category));
        $templates = self::TEMPLATES[$key] ?? self::TEMPLATES['default'];

        // Pick a deterministic-but-varied line from each group using
        // a hash of the name so the same product always gets the same
        // description, while different products get different ones.
        $hash = crc32(strtolower($name));

        $opener = $templates['openers'][abs($hash) % count($templates['openers'])];
        $middle = $templates['middles'][abs($hash + 1) % count($templates['middles'])];
        $closer = $templates['closers'][abs($hash + 2) % count($templates['closers'])];

        $opener = str_replace('{name}', $name, $opener);

        return "{$opener} {$middle} {$closer}";
    }
}
