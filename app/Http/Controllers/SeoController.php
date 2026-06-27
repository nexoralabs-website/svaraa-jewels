<?php

namespace App\Http\Controllers;

use App\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function __construct(protected SeoService $seoService) {}

    /**
     * Serve dynamically generated sitemap.xml
     */
    public function sitemap(): Response
    {
        $xml = $this->seoService->generateSitemap();

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Serve robots.txt
     */
    public function robots(): Response
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /orders',
            'Disallow: /account',
            'Disallow: /profile',
            '',
            'Sitemap: ' . route('seo.sitemap'),
        ]);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
