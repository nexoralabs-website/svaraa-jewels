<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SeoService
{
    /**
     * Build SEO meta array for a product page.
     */
    public function forProduct(Product $product): array
    {
        $title       = $product->meta_title ?: $product->name . ' | ' . config('app.name');
        $description = $product->meta_description
            ?: (strlen($product->description ?? '') > 160
                ? substr($product->description, 0, 157) . '...'
                : ($product->description ?? $title));

        $image = $product->og_image
            ? asset('storage/' . $product->og_image)
            : ($product->thumbnail ? asset('storage/' . $product->thumbnail) : asset('images/og-default.jpg'));

        $url = route('products.show', $product->slug);

        return [
            'title'             => $title,
            'description'       => $description,
            'canonical'         => $url,
            'og_title'          => $title,
            'og_description'    => $description,
            'og_image'          => $image,
            'og_url'            => $url,
            'og_type'           => 'product',
            'json_ld'           => $this->productJsonLd($product, $title, $description, $image, $url),
        ];
    }

    /**
     * Build SEO meta array for a category page.
     */
    public function forCategory(Category $category): array
    {
        $title       = $category->meta_title ?: $category->name . ' Jewellery | ' . config('app.name');
        $description = $category->meta_description
            ?: 'Shop our exclusive ' . $category->name . ' collection at ' . config('app.name') . '. Handcrafted luxury jewellery for every occasion.';
        $url         = route('categories.show', $category->slug);

        return [
            'title'          => $title,
            'description'    => $description,
            'canonical'      => $url,
            'og_title'       => $title,
            'og_description' => $description,
            'og_image'       => asset('images/og-default.jpg'),
            'og_url'         => $url,
            'og_type'        => 'website',
            'json_ld'        => null,
        ];
    }

    /**
     * Build default SEO meta for generic pages.
     */
    public function defaults(string $title = '', string $description = ''): array
    {
        $appName = config('app.name');
        $t       = $title ? "{$title} | {$appName}" : $appName;
        $d       = $description ?: 'Discover handcrafted luxury earrings at ' . $appName . '. Everyday elegance and special moments, crafted for you.';

        return [
            'title'          => $t,
            'description'    => $d,
            'canonical'      => url()->current(),
            'og_title'       => $t,
            'og_description' => $d,
            'og_image'       => asset('images/og-default.jpg'),
            'og_url'         => url()->current(),
            'og_type'        => 'website',
            'json_ld'        => null,
        ];
    }

    /**
     * Generate the sitemap XML.
     */
    public function generateSitemap(): string
    {
        return Cache::remember('sitemap_xml', 3600, function () {
            $lines   = [];
            $lines[] = '<?xml version="1.0" encoding="UTF-8"?>';
            $lines[] = '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

            // Static pages
            foreach ($this->staticPages() as $page) {
                $lines[] = $this->urlEntry($page['loc'], $page['lastmod'], $page['changefreq'], $page['priority']);
            }

            // Product pages
            Product::where('status', true)->select('slug', 'updated_at')->each(function ($p) use (&$lines) {
                $lines[] = $this->urlEntry(
                    route('products.show', $p->slug),
                    $p->updated_at->toDateString(),
                    'weekly',
                    '0.8'
                );
            });

            // Category pages — earrings only
            Category::where('status', true)->where('slug', 'earrings')->select('slug', 'updated_at')->each(function ($c) use (&$lines) {
                $lines[] = $this->urlEntry(
                    route('categories.show', $c->slug),
                    $c->updated_at->toDateString(),
                    'weekly',
                    '0.7'
                );
            });

            $lines[] = '</urlset>';
            return implode("\n", $lines);
        });
    }

    private function staticPages(): array
    {
        return [
            ['loc' => route('home'),           'lastmod' => now()->toDateString(), 'changefreq' => 'daily',   'priority' => '1.0'],
            ['loc' => route('products.index'), 'lastmod' => now()->toDateString(), 'changefreq' => 'daily',   'priority' => '0.9'],
        ];
    }

    private function urlEntry(string $loc, string $lastmod, string $changefreq, string $priority): string
    {
        return "  <url>\n    <loc>{$loc}</loc>\n    <lastmod>{$lastmod}</lastmod>\n    <changefreq>{$changefreq}</changefreq>\n    <priority>{$priority}</priority>\n  </url>";
    }

    private function productJsonLd(Product $product, string $title, string $description, string $image, string $url): array
    {
        $ld = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product->name,
            'description' => $description,
            'image'       => $image,
            'url'         => $url,
            'sku'         => 'SVR-' . $product->id,
            'brand'       => ['@type' => 'Brand', 'name' => config('app.name')],
            'offers'      => [
                '@type'         => 'Offer',
                'priceCurrency' => 'INR',
                'price'         => number_format((float) $product->price, 2, '.', ''),
                'availability'  => $product->stock > 0
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'url'           => $url,
            ],
        ];

        $avg   = $product->getAverageRatingAttribute();
        $count = $product->getReviewCountAttribute();

        if ($count > 0) {
            $ld['aggregateRating'] = [
                '@type'       => 'AggregateRating',
                'ratingValue' => $avg,
                'reviewCount' => $count,
            ];
        }

        return $ld;
    }
}
