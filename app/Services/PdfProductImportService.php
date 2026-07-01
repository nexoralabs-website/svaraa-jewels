<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Parses a PDF and returns a deduplicated array of product candidates.
 *
 * Key guarantees:
 *   - ALL pages processed, never aborted on partial failure.
 *   - ALL product-like images extracted per page (not just the first/largest).
 *   - One page may produce multiple candidates (e.g. 2 earrings side-by-side).
 *   - Three-layer deduplication collapses identical/near-identical images into
 *     a single row, recording every page number where the image appeared.
 *   - Pages with no qualifying image are SKIPPED (no placeholder rows).
 *   - Candidate shape includes `pages_found` (all page numbers, incl. first).
 *   - Stored filenames are URL-safe (spaces removed) so preview_url never 404s.
 *
 * Filtering thresholds (product-like image criteria):
 *   MIN_WIDTH / MIN_HEIGHT : 150 px  (small earring close-ups pass)
 *   MAX_ASPECT_RATIO       : 4.0     (portrait or landscape, not a banner)
 *   MIN_PAGE_AREA_PCT      : 1 %     (relaxed to catch small catalogue thumbnails)
 *   MIN_BYTES              : 512 B   (reject empty/corrupt streams only)
 */
class PdfProductImportService
{
    // ── Image format signatures ────────────────────────────────────────────
    private const IMAGE_SIGNATURES = [
        'jpeg' => "\xFF\xD8\xFF",
        'png'  => "\x89PNG\r\n",
        'webp' => 'RIFF',
    ];

    // ── Product-image quality thresholds ──────────────────────────────────
    private const MIN_WIDTH       = 150;    // px  (relaxed: small earring photos pass)
    private const MIN_HEIGHT      = 150;    // px
    private const MAX_ASPECT      = 4.0;    // width/height or height/width
    private const MIN_AREA_PCT    = 1.0;    // % of page area (relaxed from 5%)
    private const A4_AREA         = 595 * 842;
    private const MIN_BYTES       = 512;    // 512 B — relaxed from 2 KB

    // ── Deduplication thresholds ──────────────────────────────────────────
    private const PHASH_HAMMING   = 3;      // Hamming distance ≤ 3 = near-duplicate
    /** Number of buckets per channel = 4; shift = 8-2 = 6 to map 0-255 → 0-3 */
    private const COLOR_BINS      = 4;
    private const COLOR_SHIFT     = 6;      // 8 - log2(COLOR_BINS) = 8 - 2

    // ── Premium name vocabulary ───────────────────────────────────────────
    private const NAME_PREFIXES = [
        'Golden', 'Classic', 'Twilight', 'Celeste', 'Royal', 'Antique',
        'Bridal', 'Elegant', 'Delicate', 'Vintage', 'Shimmer', 'Pearl',
        'Bloom', 'Dazzle', 'Radiant', 'Ornate', 'Festive', 'Heritage',
    ];
    private const NAME_SUFFIXES = [
        'Earrings', 'Studs', 'Drops', 'Hoops', 'Danglers', 'Jhumkas',
        'Chandbalis', 'Bali', 'Kada Earrings', 'Drop Earrings',
        'Stud Earrings', 'Hoop Earrings',
    ];

    // ── Per-call deduplication state ──────────────────────────────────────
    /** sha256 → index in $candidates array */
    private array $seenSha = [];
    /** sha256 → pHash int|null */
    private array $seenPHashes = [];
    /** sha256 → colour-histogram array */
    private array $seenColors = [];

    // ── Public summary counters (read by BulkUploadProducts after call) ───
    public int $pagesScanned     = 0;
    public int $extractedCount   = 0;
    public int $duplicateCount   = 0;
    public int $placeholderCount = 0;
    public int $failedCount      = 0;

    public function __construct(
        private readonly ProductDescriptionService $descService,
    ) {}

    // ──────────────────────────────────────────────────────────────────────
    // Public entry point
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Parse one PDF and return deduplicated product candidates.
     *
     * @throws \RuntimeException  for encrypted or unreadable PDFs.
     * @return array<int, array>  Livewire-serialisable candidate maps.
     */
    public function extractCandidates(string $absolutePdfPath, string $pdfStoredPath): array
    {
        $this->guardFile($absolutePdfPath);

        $this->seenSha       = [];
        $this->seenPHashes   = [];
        $this->seenColors    = [];
        $this->pagesScanned  = 0;
        $this->extractedCount   = 0;
        $this->duplicateCount   = 0;
        $this->placeholderCount = 0;
        $this->failedCount      = 0;

        $config = new \Smalot\PdfParser\Config();
        $config->setFontSpaceLimit(-50);
        $config->setRetainImageContent(true);

        $parser = new PdfParser([], $config);

        try {
            $pdf = $parser->parseFile($absolutePdfPath);
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not parse PDF: ' . $e->getMessage(), 0, $e);
        }

        $details = $pdf->getDetails();
        if (! empty($details['Encrypt'])) {
            throw new \RuntimeException(
                'Encrypted PDFs are not supported. Please remove the password and re-upload.'
            );
        }

        $pages = $pdf->getPages();
        if (count($pages) === 0) {
            throw new \RuntimeException('The PDF contains no pages.');
        }

        Log::info('PDF IMPORT START', [
            'pdf'=>$pdfStoredPath,
        ]);

        $pdfBaseName = pathinfo($pdfStoredPath, PATHINFO_FILENAME);
        // $candidates is an ordered list; dedup patches it by reference.
        $candidates  = [];

        foreach ($pages as $pageIndex => $page) {
            $pageNumber = $pageIndex + 1;
            Log::info('PDF PAGE START', ['page'=>$pageNumber]);
            $this->pagesScanned++;

            // ── Text extraction (best-effort; never fatal) ────────────────
            $rawText = '';
            try {
                $rawText = $page->getText();
            } catch (\Throwable) {
            }
            $detectedPrice = $this->extractPrice($rawText);

            // ── Pull ALL product-like images from this page ───────────────
            $pageImages = [];
            try {
                $pageImages = $this->extractAllProductImages($page);
                Log::info('IMAGE EXTRACTED', ['page'=>$pageNumber, 'count'=>count($pageImages)]);
            } catch (\Throwable) {
                $this->failedCount++;
            }

            if (empty($pageImages)) {
                $this->placeholderCount++;
                continue;
            }

            logger()->info('pdf_extraction_page', [
                'page'   => $pageNumber,
                'images' => count($pageImages),
                'price'  => $detectedPrice,
                'pdf'    => $pdfBaseName,
            ]);

            // Sequential counter for unique-per-page suffix (0-based, dense)
            $uniqueOnPage = 0;

            foreach ($pageImages as $imgData) {
                ['bytes' => $bytes, 'ext' => $ext] = $imgData;
                $sha = hash('sha256', $bytes);

                // ── Layer 1: exact SHA-256 duplicate ──────────────────
                if (isset($this->seenSha[$sha])) {
                    $idx = $this->seenSha[$sha];
                    if (! in_array($pageNumber, $candidates[$idx]['pages_found'], true)) {
                        $candidates[$idx]['pages_found'][] = $pageNumber;
                    }
                    if (! in_array($pageNumber, $candidates[$idx]['duplicate_pages'], true)) {
                        $candidates[$idx]['duplicate_pages'][] = $pageNumber;
                    }
                    $candidates[$idx]['occurrences_count']++;
                    $this->duplicateCount++;
                    continue;
                }

                // ── Layer 2: pHash near-duplicate (Hamming ≤ 3) ───────
                $pHash = $this->computePHash($bytes);
                $isDup = false;
                if ($pHash !== null) {
                    foreach ($this->seenPHashes as $existingSha => $existingPHash) {
                        if ($existingPHash !== null
                            && $this->hammingDistance($pHash, $existingPHash) <= self::PHASH_HAMMING
                        ) {
                            $idx = $this->seenSha[$existingSha];
                            if (! in_array($pageNumber, $candidates[$idx]['pages_found'], true)) {
                                $candidates[$idx]['pages_found'][] = $pageNumber;
                            }
                            if (! in_array($pageNumber, $candidates[$idx]['duplicate_pages'], true)) {
                                $candidates[$idx]['duplicate_pages'][] = $pageNumber;
                            }
                            $candidates[$idx]['occurrences_count']++;
                            $this->duplicateCount++;
                            $isDup = true;
                            break;
                        }
                    }
                }
                if ($isDup) {
                    continue;
                }

                // ── Layer 3: dominant-colour near-duplicate ────────────
                $colors = $this->computeColorHistogram($bytes);
                if ($colors !== null) {
                    foreach ($this->seenColors as $existingSha => $existingColors) {
                        if ($this->colorHistogramSimilar($colors, $existingColors)) {
                            $existingPHash2 = $this->seenPHashes[$existingSha] ?? null;
                            $pHashClose = ($existingPHash2 === null || $pHash === null)
                                ? true
                                : $this->hammingDistance($pHash, $existingPHash2) <= self::PHASH_HAMMING + 2;
                            if ($pHashClose) {
                                $idx = $this->seenSha[$existingSha];
                                if (! in_array($pageNumber, $candidates[$idx]['pages_found'], true)) {
                                    $candidates[$idx]['pages_found'][] = $pageNumber;
                                }
                                if (! in_array($pageNumber, $candidates[$idx]['duplicate_pages'], true)) {
                                    $candidates[$idx]['duplicate_pages'][] = $pageNumber;
                                }
                                $candidates[$idx]['occurrences_count']++;
                                $this->duplicateCount++;
                                $isDup = true;
                                break;
                            }
                        }
                    }
                }
                if ($isDup) {
                    continue;
                }

                // ── Unique image — persist ─────────────────────────────
                // Use a dense sequential suffix to avoid filename collisions
                // when the page has multiple unique images. Sanitise the PDF
                // basename so spaces never appear in the stored path / URL.
                $safeName = $this->sanitiseFilename($pdfBaseName);
                $suffix   = $uniqueOnPage > 0 ? "-img{$uniqueOnPage}" : '';
                $filename = "products/{$safeName}-p{$pageNumber}{$suffix}.{$ext}";
                Storage::disk('public')->makeDirectory('products');
                $optimizedBytes = $this->resizeImage($bytes, $ext);
                Storage::disk('public')->put($filename, $optimizedBytes);

                // Verify file exists immediately after write
                if (!Storage::disk('public')->exists($filename)) {
                    throw new \RuntimeException("Image written but file missing: {$filename}");
                }

                Log::info('IMAGE SAVED', [
                    'stored_path'=>$filename,
                    'exists'=>Storage::disk('public')->exists($filename),
                    'absolute'=>Storage::disk('public')->path($filename),
                    'url'=>Storage::url($filename),
                    'size'=>Storage::disk('public')->size($filename),
                ]);

                $this->seenSha[$sha]     = count($candidates);
                $this->seenPHashes[$sha] = $pHash;
                if ($colors !== null) {
                    $this->seenColors[$sha] = $colors;
                }

                $name = $this->generateProductName($rawText, $pageNumber);
                $candidates[] = $this->buildCandidate(
                    storedPath:    $filename,
                    name:          $name,
                    price:         $detectedPrice,
                    pageNumber:    $pageNumber,
                    fromEmbedded:  true,
                    sha256:        $sha,
                    pdfBaseName:   $safeName,
                    isPlaceholder: false,
                );
                $this->extractedCount++;
                $uniqueOnPage++;
            }
        }

        Log::info('PDF IMPORT COMPLETE', [
            'count'=>count($candidates),
            'images'=>$candidates,
        ]);

        return $candidates;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Image extraction — extract ALL product-like images from one page
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Return every qualifying product image found on the page.
     * "Qualifying" = passes dimension, aspect-ratio, area and byte-size filters.
     *
     * @return array<int, array{bytes: string, ext: string}>
     */
    private function extractAllProductImages(\Smalot\PdfParser\Page $page): array
    {
        $raw = $this->collectRawImageCandidates($page);

        $qualified = [];
        foreach ($raw as $item) {
            if ($this->isProductImage($item['bytes'])) {
                $qualified[] = $item;
            }
        }

        // Sort largest-first so that if two images are near-duplicates,
        // the higher-quality one becomes the keeper.
        usort($qualified, fn ($a, $b) => strlen($b['bytes']) <=> strlen($a['bytes']));

        return $qualified;
    }

    /**
     * Collect every raw image blob from all XObjects + raw stream scan.
     * Does NOT filter — returns everything that has a valid image header.
     *
     * @return array<int, array{bytes: string, ext: string}>
     */
    private function collectRawImageCandidates(\Smalot\PdfParser\Page $page): array
    {
        $found = [];
        $seenBytes = []; // avoid returning exact duplicate blobs from same page

        // Strategy A: XObjects (the primary smalot path)
        try {
            foreach ($page->getXObjects() as $xObj) {
                $item = $this->tryExtractFromXObject($xObj);
                if ($item !== null) {
                    $sig = hash('sha256', $item['bytes']);
                    if (! isset($seenBytes[$sig])) {
                        $seenBytes[$sig] = true;
                        $found[] = $item;
                    }
                }
            }
        } catch (\Throwable) {
        }

        // Strategy B: raw stream scan (catches images not exposed as XObjects)
        try {
            $streamItems = $this->scanRawPageStreamAll($page);
            foreach ($streamItems as $item) {
                $sig = hash('sha256', $item['bytes']);
                if (! isset($seenBytes[$sig])) {
                    $seenBytes[$sig] = true;
                    $found[] = $item;
                }
            }
        } catch (\Throwable) {
        }

        return $found;
    }

    /**
     * Try to extract image bytes from one XObject via multiple accessor paths.
     */
    private function tryExtractFromXObject(object $xObj): ?array
    {
        $content = null;

        if (method_exists($xObj, 'getContent')) {
            try { $content = $xObj->getContent(); } catch (\Throwable) {}
        }
        if (blank($content) && method_exists($xObj, 'getData')) {
            try { $content = $xObj->getData(); } catch (\Throwable) {}
        }
        if (blank($content) && method_exists($xObj, 'get')) {
            try { $content = $xObj->get('data'); } catch (\Throwable) {}
        }

        if (blank($content)) {
            return null;
        }

        $ext  = $this->detectImageExtension($content);
        if ($ext === null) {
            return null;
        }
        $data = $this->cleanImageData($content, $ext);
        if (strlen($data) < self::MIN_BYTES) {
            return null;
        }

        return ['bytes' => $data, 'ext' => $ext];
    }

    /**
     * Walk the concatenated raw content of all XObjects looking for image
     * signatures. Returns ALL matches, not just the largest.
     *
     * @return array<int, array{bytes: string, ext: string}>
     */
    private function scanRawPageStreamAll(\Smalot\PdfParser\Page $page): array
    {
        // Gather raw bytes
        $raw = '';
        try {
            if (method_exists($page, 'getDataStream')) {
                $raw = (string) $page->getDataStream();
            }
        } catch (\Throwable) {}

        if (blank($raw)) {
            try {
                $parts = [];
                foreach ($page->getXObjects() as $xo) {
                    if (method_exists($xo, 'getContent')) {
                        $parts[] = (string) $xo->getContent();
                    }
                }
                $raw = implode('', $parts);
            } catch (\Throwable) {}
        }

        if (blank($raw)) {
            return [];
        }

        $results = [];
        foreach (self::IMAGE_SIGNATURES as $ext => $sig) {
            $pos = 0;
            while (($pos = strpos($raw, $sig, $pos)) !== false) {
                $chunk   = substr($raw, $pos, 10 * 1024 * 1024);
                $cleaned = $this->cleanImageData($chunk, $ext);
                if (strlen($cleaned) >= self::MIN_BYTES) {
                    $results[] = ['bytes' => $cleaned, 'ext' => $ext];
                }
                $pos++;
            }
        }
        return $results;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Product-image quality filter
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Return true if the image bytes represent a product-like image:
     *   - Valid, decodable by GD
     *   - Width  ≥ MIN_WIDTH  and  Height ≥ MIN_HEIGHT
     *   - Aspect ratio ≤ MAX_ASPECT  (not a banner/strip)
     *   - Occupies ≥ MIN_AREA_PCT of nominal A4 page
     *   - At least MIN_BYTES raw bytes
     */
    private function isProductImage(string $bytes): bool
    {
        if (strlen($bytes) < self::MIN_BYTES) {
            return false;
        }
        // Use GD getimagesizefromstring — fast, no full decode needed
        try {
            $info = @getimagesizefromstring($bytes);
        } catch (\Throwable) {
            return false;
        }
        if (! $info || $info[0] === 0 || $info[1] === 0) {
            return false;
        }

        $w = $info[0];
        $h = $info[1];

        if ($w < self::MIN_WIDTH || $h < self::MIN_HEIGHT) {
            return false;
        }

        $aspect = max($w, $h) / min($w, $h);
        if ($aspect > self::MAX_ASPECT) {
            return false;
        }

        $areaPct = ($w * $h) / self::A4_AREA * 100.0;
        if ($areaPct < self::MIN_AREA_PCT) {
            return false;
        }

        return true;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Perceptual hash (Layer 2)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Compute a 64-bit average-hash (aHash) using GD.
     * Resize to 8×8 greyscale → compare each pixel to mean.
     * Returns null on any failure (degrades gracefully to SHA-only dedup).
     */
    public function computePHash(string $imageBytes): ?int
    {
        try {
            $im = @imagecreatefromstring($imageBytes);
            if ($im === false) {
                return null;
            }

            $small = imagecreatetruecolor(8, 8);
            imagecopyresampled($small, $im, 0, 0, 0, 0, 8, 8, imagesx($im), imagesy($im));
            imagedestroy($im);

            $pixels = [];
            for ($y = 0; $y < 8; $y++) {
                for ($x = 0; $x < 8; $x++) {
                    $rgb      = imagecolorat($small, $x, $y);
                    $r        = ($rgb >> 16) & 0xFF;
                    $g        = ($rgb >> 8)  & 0xFF;
                    $b        = $rgb & 0xFF;
                    $pixels[] = (int) (0.299 * $r + 0.587 * $g + 0.114 * $b);
                }
            }
            imagedestroy($small);

            $mean = array_sum($pixels) / 64;
            $hash = 0;
            foreach ($pixels as $i => $val) {
                if ($val >= $mean) {
                    $hash |= (1 << $i);
                }
            }

            return $hash;
        } catch (\Throwable) {
            return null;
        }
    }

    /** Count differing bits between two 64-bit integers. */
    public function hammingDistance(int $a, int $b): int
    {
        $xor   = $a ^ $b;
        $count = 0;
        while ($xor !== 0) {
            $count += $xor & 1;
            $xor >>= 1;
        }
        return $count;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Colour histogram (Layer 3)
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Build a normalised 4×4×4 RGB colour histogram (64 buckets).
     * Returns null on failure.
     *
     * @return float[]|null
     */
    public function computeColorHistogram(string $imageBytes): ?array
    {
        try {
            $im = @imagecreatefromstring($imageBytes);
            if ($im === false) {
                return null;
            }

            // Resize to 32×32 for speed
            $thumb = imagecreatetruecolor(32, 32);
            imagecopyresampled($thumb, $im, 0, 0, 0, 0, 32, 32, imagesx($im), imagesy($im));
            imagedestroy($im);

            $bins  = array_fill(0, 64, 0);
            $total = 32 * 32;

            for ($y = 0; $y < 32; $y++) {
                for ($x = 0; $x < 32; $x++) {
                    $rgb = imagecolorat($thumb, $x, $y);
                    $r   = (($rgb >> 16) & 0xFF) >> self::COLOR_SHIFT; // 0-3
                    $g   = (($rgb >> 8)  & 0xFF) >> self::COLOR_SHIFT;
                    $b   = ($rgb & 0xFF)          >> self::COLOR_SHIFT;
                    $idx = $r * self::COLOR_BINS * self::COLOR_BINS + $g * self::COLOR_BINS + $b;
                    $bins[$idx]++;
                }
            }
            imagedestroy($thumb);

            // Normalise to [0,1]
            return array_map(fn (int $v) => $v / $total, $bins);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Return true if two colour histograms are similar.
     * Uses L1 (sum of absolute differences) ≤ 0.35 as threshold.
     *
     * @param float[] $a
     * @param float[] $b
     */
    private function colorHistogramSimilar(array $a, array $b): bool
    {
        $l1 = 0.0;
        foreach ($a as $i => $v) {
            $l1 += abs($v - ($b[$i] ?? 0.0));
        }
        return $l1 <= 0.35;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Product name generation
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Generate a short, premium jewellery product name.
     * Priority: clean title from page text → keyword composition → vocabulary fallback.
     * Never returns a raw PDF filename or "Untitled …" strings.
     */
    public function generateProductName(string $pageText, int $pageNumber): string
    {
        $text = trim(preg_replace('/\s+/', ' ', $pageText));

        if (! blank($text)) {
            $lines = array_filter(
                array_map('trim', explode("\n", $text)),
                fn (string $l) => strlen($l) >= 4 && strlen($l) <= 50
            );

            foreach ($lines as $line) {
                if (preg_match('/^[\d\s\-\\\/.:,@#$\(\)]+$/', $line)) {
                    continue;
                }
                if (preg_match('/\.(pdf|jpg|jpeg|png|ai|psd|svg)/i', $line)) {
                    continue;
                }
                if (preg_match('/^(page|untitled|new\s+file|draft|copy|design)/i', $line)) {
                    continue;
                }
                if (preg_match('/earring|jhumka|stud|hoop|drop|chandbali|bali|jewel|gold|silver|kada/i', $line)) {
                    return Str::title(strtolower(Str::limit($line, 50, '')));
                }
                $wordCount = str_word_count($line);
                if ($wordCount >= 2 && $wordCount <= 6) {
                    return Str::title(strtolower(Str::limit($line, 50, '')));
                }
            }
        }

        $lower = strtolower($text);
        if (str_contains($lower, 'jhumka') || str_contains($lower, 'jhumki')) {
            return 'Classic Jhumka Earrings';
        }
        if (str_contains($lower, 'chandbali')) {
            return 'Antique Chandbali Earrings';
        }
        if (str_contains($lower, 'hoop') || str_contains($lower, 'bali')) {
            return 'Elegant Hoop Earrings';
        }
        if (str_contains($lower, 'stud')) {
            return 'Premium Stud Earrings';
        }
        if (str_contains($lower, 'drop') || str_contains($lower, 'dangle')) {
            return 'Graceful Drop Earrings';
        }

        $seed   = crc32($pageNumber . '-earring-svaraa');
        $prefix = self::NAME_PREFIXES[abs($seed) % count(self::NAME_PREFIXES)];
        $suffix = self::NAME_SUFFIXES[abs($seed + 7) % count(self::NAME_SUFFIXES)];

        return "{$prefix} {$suffix}";
    }

    // ──────────────────────────────────────────────────────────────────────
    // Candidate builder
    // ──────────────────────────────────────────────────────────────────────

    private function buildCandidate(
        string $storedPath,
        string $name,
        string $price,
        int    $pageNumber,
        bool   $fromEmbedded,
        string $sha256,
        string $pdfBaseName,
        bool   $isPlaceholder,
    ): array {
        // Use root-relative URL (/storage/...) so previews work regardless of APP_URL.
        // Storage::disk('public')->url() prepends APP_URL which may be a placeholder domain.
        $previewUrl = '/storage/' . ltrim($storedPath, '/');

        return [
            'stored_path'       => $storedPath,
            'preview_url'       => $previewUrl,
            'name'              => $name,
            'price'             => $price,
            'source'            => 'pdf',
            'pdf_page'          => $pageNumber,
            'pages_found'       => [$pageNumber],   // grows as duplicates are merged
            'from_embedded'     => $fromEmbedded,
            'sha256'            => $sha256,
            'source_pdf'        => $pdfBaseName,
            'duplicate_pages'   => [],              // page numbers after first occurrence
            'occurrences_count' => 1,
            'is_placeholder'    => $isPlaceholder,
        ];
    }

    // ──────────────────────────────────────────────────────────────────────
    // GD placeholder
    // ──────────────────────────────────────────────────────────────────────

    private function generatePagePlaceholder(string $baseName, int $pageNumber): string
    {
        $w  = 400;
        $h  = 400;
        $im = imagecreatetruecolor($w, $h);

        $bg    = imagecolorallocate($im, 245, 235, 221);
        $dark  = imagecolorallocate($im, 46,  26,  18);
        $gold  = imagecolorallocate($im, 200, 163, 93);
        $red   = imagecolorallocate($im, 110, 15,  18);
        $white = imagecolorallocate($im, 255, 255, 255);

        imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bg);
        imagerectangle($im, 8,  8,  $w - 9,  $h - 9,  $gold);
        imagerectangle($im, 10, 10, $w - 11, $h - 11, $gold);

        $cx = (int) ($w / 2);
        $cy = (int) ($h / 2) - 30;
        imagefilledellipse($im, $cx, $cy, 80, 80, $red);
        imagestring($im, 5, $cx - 12, $cy - 8, 'PDF', $white);

        $label = "Page {$pageNumber}";
        imagestring($im, 4, (int) (($w - strlen($label) * 8) / 2), $cy + 55, $label, $dark);

        $hint = 'Replace image before publishing';
        imagestring($im, 2, (int) (($w - strlen($hint) * 6) / 2), $h - 30, $hint, $gold);

        ob_start();
        imagejpeg($im, null, 90);
        $jpeg = (string) ob_get_clean();
        imagedestroy($im);

        Storage::disk('public')->makeDirectory('products');
        $path = "products/{$baseName}-p{$pageNumber}-placeholder.jpg";
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Image format detection / cleaning
    // ──────────────────────────────────────────────────────────────────────

    private function detectImageExtension(string $data): ?string
    {
        foreach (self::IMAGE_SIGNATURES as $ext => $sig) {
            if (str_starts_with($data, $sig)) {
                return $ext;
            }
        }
        if (strlen($data) > 12 && substr($data, 8, 4) === 'WEBP') {
            return 'webp';
        }
        return null;
    }

    private function cleanImageData(string $data, ?string $ext): string
    {
        if ($ext === null) {
            return $data;
        }
        $sig = self::IMAGE_SIGNATURES[$ext] ?? '';
        $pos = strpos($data, $sig);
        return $pos !== false ? substr($data, $pos) : $data;
    }

    private function resizeImage(string $bytes, string $ext): string
    {
        // Target: ≤ 1200 px on the longest side, JPEG/WebP at quality 75.
        // Re-encode ALWAYS — PDF-extracted streams can be multi-MB at small pixel
        // dimensions because they are stored as uncompressed or near-lossless JPEG.
        // Unconditional re-encode shrinks those from 3-4 MB to ~200-400 KB.
        $maxDim   = 1200;
        $jpegQual = 75;
        $webpQual = 75;
        $pngLevel = 8;   // 0 = no compression, 9 = max

        try {
            $im = @imagecreatefromstring($bytes);
            if ($im === false) {
                return $bytes;
            }

            $w = imagesx($im);
            $h = imagesy($im);

            // Resize down if either dimension exceeds the limit
            if ($w > $maxDim || $h > $maxDim) {
                $ratio = min($maxDim / $w, $maxDim / $h);
                $newW  = max(1, (int) round($w * $ratio));
                $newH  = max(1, (int) round($h * $ratio));

                $resized = imagecreatetruecolor($newW, $newH);
                // Preserve PNG/WebP transparency
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);
                imagedestroy($im);
                $im = $resized;
            }

            // Always re-encode at target quality
            ob_start();
            if ($ext === 'jpeg' || $ext === 'jpg') {
                imagejpeg($im, null, $jpegQual);
            } elseif ($ext === 'png') {
                imagepng($im, null, $pngLevel);
            } elseif ($ext === 'webp') {
                imagewebp($im, null, $webpQual);
            } else {
                // Unknown type — skip re-encode
                ob_end_clean();
                imagedestroy($im);
                return $bytes;
            }
            $output = ob_get_clean();
            imagedestroy($im);

            return ($output !== false && $output !== '') ? $output : $bytes;

        } catch (\Throwable) {
            return $bytes;
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Price extraction
    // ──────────────────────────────────────────────────────────────────────

    public function extractPrice(string $text): string
    {
        $patterns = [
            '/[₹]\s*([0-9][0-9,\.]+)/u',
            '/(?:Rs\.?|INR)\s*([0-9][0-9,\.]+)/i',
            '/(?:Price|MRP|Cost|Rate)[^\d]*([0-9][0-9,\.]+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $m)) {
                $raw = str_replace(',', '', $m[1]);
                if (is_numeric($raw) && (float) $raw > 0) {
                    return $raw;
                }
            }
        }
        return '';
    }

    // ──────────────────────────────────────────────────────────────────────
    // File guard
    // ──────────────────────────────────────────────────────────────────────

    private function guardFile(string $path): void
    {
        if (! file_exists($path)) {
            throw new \RuntimeException("PDF file not found: {$path}");
        }
        if (! is_readable($path)) {
            throw new \RuntimeException("PDF file is not readable: {$path}");
        }

        $mime = mime_content_type($path);
        if ($mime !== 'application/pdf' && $mime !== 'application/x-pdf') {
            throw new \RuntimeException(
                "File does not appear to be a valid PDF (detected: {$mime})."
            );
        }

        $header = file_get_contents($path, false, null, 0, 1024);
        if ($header === false) {
            throw new \RuntimeException('Cannot read PDF file.');
        }
        if (str_contains($header, '/Encrypt')) {
            throw new \RuntimeException('Encrypted / password-protected PDFs are not supported.');
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Filename sanitiser
    // ──────────────────────────────────────────────────────────────────────

    /**
     * Strip/replace characters that are illegal or problematic in URLs and
     * filesystems. Spaces become hyphens; non-ASCII is dropped; runs of
     * hyphens/underscores are collapsed.
     *
     * "Untitled design_20260624_203044_0000" → "Untitled-design_20260624_203044_0000"
     * Ensures preview_url never contains a literal space.
     */
    private function sanitiseFilename(string $name): string
    {
        // Replace spaces and common unsafe chars with a single hyphen
        $safe = preg_replace('/[\s\x00-\x1F\x7F]+/', '-', $name);
        // Remove characters not safe in URLs/filenames (keep A-Z a-z 0-9 - _ .)
        $safe = preg_replace('/[^A-Za-z0-9\-_.]/', '', $safe ?? '');
        // Collapse multiple consecutive hyphens/underscores
        $safe = preg_replace('/[-]{2,}/', '-', $safe ?? '');
        // Trim leading/trailing separators
        $safe = trim($safe ?? '', '-_.');
        // Fallback if the name was entirely non-ASCII
        return $safe !== '' ? $safe : 'pdf-' . substr(md5($name), 0, 8);
    }
}