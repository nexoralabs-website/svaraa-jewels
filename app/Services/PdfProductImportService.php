<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser as PdfParser;

/**
 * Parses a PDF and returns a deduplicated array of product candidates.
 *
 * IMPORTANT: For debugging — filtering temporarily disabled to capture all images.
 */
class PdfProductImportService
{
    // ── Image format signatures ────────────────────────────────────────────
    private const IMAGE_SIGNATURES = [
        'jpeg' => "\xFF\xD8\xFF",
        'png'  => "\x89PNG\r\n",
        'webp' => 'RIFF',
    ];

    // ── Per-call deduplication state ──────────────────────────────────────
    private array $seenSha = [];
    private array $seenPHashes = [];
    private array $seenColors = [];

    // ── Public summary counters ───────────────────────────────────────────
    public int $pagesScanned   = 0;
    public int $extractedCount = 0;
    public int $duplicateCount = 0;
    public int $placeholderCount = 0;
    public int $failedCount    = 0;

    public function __construct(
        private readonly ProductDescriptionService $descService,
    ) {}

    public function extractCandidates(string $pdfStoredPath): array
    {
        $this->seenSha      = [];
        $this->seenPHashes  = [];
        $this->seenColors   = [];
        $this->pagesScanned = 0;
        $this->extractedCount  = 0;
        $this->duplicateCount  = 0;
        $this->placeholderCount = 0;
        $this->failedCount     = 0;

        // Get PDF content from storage
        $pdfContent = Storage::disk('public')->get($pdfStoredPath);
        if ($pdfContent === null) {
            throw new \RuntimeException("PDF file not found: {$pdfStoredPath}");
        }

        // Create temporary file for parsing
        $tempPdfPath = tempnam(sys_get_temp_dir(), 'pdf_import_');
        if ($tempPdfPath === false) {
            throw new \RuntimeException('Could not create temporary file for PDF import');
        }
        file_put_contents($tempPdfPath, $pdfContent);

        $config = new \Smalot\PdfParser\Config();
        $config->setFontSpaceLimit(-50);
        $config->setRetainImageContent(true);

        $parser = new PdfParser([], $config);

        try {
            $pdf = $parser->parseFile($tempPdfPath);
        } catch (\Exception $e) {
            unlink($tempPdfPath);
            throw new \RuntimeException('Could not parse PDF: ' . $e->getMessage(), 0, $e);
        }

        $details = $pdf->getDetails();
        if (! empty($details['Encrypt'])) {
            unlink($tempPdfPath);
            throw new \RuntimeException('Encrypted PDFs are not supported.');
        }

        $pages = $pdf->getPages();
        if (count($pages) === 0) {
            unlink($tempPdfPath);
            throw new \RuntimeException('The PDF contains no pages.');
        }

        Log::info('PDF IMPORT START', ['pdf'=>$pdfStoredPath, 'pages'=>count($pages)]);

        $pdfBaseName = pathinfo($pdfStoredPath, PATHINFO_FILENAME);
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

            // ── Log XObjects count ─────────────────────────────────────
            $xObjects = [];
            try {
                $xObjects = $page->getXObjects();
            } catch (\Throwable) {
            }
            Log::info('RAW PAGE OBJECTS', ['page'=>$pageNumber, 'count'=>count($xObjects)]);

            // ── Pull ALL raw images (filtering disabled for debugging) ───
            $pageImages = [];
            try {
                // TEMPORARILY: Get all raw images without filtering
                $raw = [];
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

                if (!blank($raw)) {
                    foreach (self::IMAGE_SIGNATURES as $ext => $sig) {
                        $pos = 0;
                        while (($pos = strpos($raw, $sig, $pos)) !== false) {
                            $chunk = substr($raw, $pos, 10 * 1024 * 1024);
                            $cleaned = $this->cleanImageData($chunk, $ext);
                            // TEMPORARILY: NO MIN_BYTES filter
                            if (strlen($cleaned) > 100) {
                                $pageImages[] = ['bytes'=>$cleaned, 'ext'=>$ext];
                            }
                            $pos++;
                        }
                    }
                }
                Log::info('IMAGE EXTRACTED', [
                    'page'=>$pageNumber,
                    'count'=>count($pageImages),
                    'total_bytes'=>array_sum(array_map(fn($i)=>strlen($i['bytes']), $pageImages))
                ]);
            } catch (\Throwable $e) {
                $this->failedCount++;
                Log::warning('PAGE EXTRACTION FAILED', ['page'=>$pageNumber, 'error'=>$e->getMessage()]);
            }

            // ── Fallback: render page to image if no embedded images ───────
            $fromEmbedded = true;
            $isPlaceholder = false;
            if (empty($pageImages)) {
                Log::info('FALLBACK RENDER USED', ['page'=>$pageNumber]);
                $fallbackBytes = $this->generateFallbackPageImageBytes();
                if ($fallbackBytes) {
                    $pageImages[] = ['bytes'=>$fallbackBytes, 'ext'=>'jpg'];
                    $fromEmbedded = false;
                    $isPlaceholder = true;
                    $this->placeholderCount++;
                }
            }

            // Sequential counter for unique-per-page suffix.
            $uniqueOnPage = 0;

            foreach ($pageImages as $imgData) {
                ['bytes'=>$bytes, 'ext'=>$ext] = $imgData;

                // Ensure GD extension available for resize
                $processedBytes = $this->resizeImage($bytes, $ext);

                $safeName = $this->sanitiseFilename($pdfBaseName);
                $suffix   = $uniqueOnPage > 0 ? "-img{$uniqueOnPage}" : '';
                $filename = "products/{$safeName}-p{$pageNumber}{$suffix}.jpg";
                Storage::disk('public')->makeDirectory('products');
                Storage::disk('public')->put($filename, $processedBytes);

                // Verify immediately
                if (!Storage::disk('public')->exists($filename)) {
                    unlink($tempPdfPath);
                    throw new \RuntimeException("Image written but file missing: {$filename}");
                }

                Log::info('IMAGE SAVED', [
                    'stored_path'=>$filename,
                    'exists'=>Storage::disk('public')->exists($filename),
                    'url'=>Storage::disk('public')->url($filename),
                    'size'=>Storage::disk('public')->size($filename),
                ]);

                $name = $this->generateProductName($rawText, $pageNumber);
                $candidates[] = $this->buildCandidate(
                    storedPath:    $filename,
                    name:        $name,
                    price:        $detectedPrice,
                    pageNumber:  $pageNumber,
                    fromEmbedded: $fromEmbedded,
                    sha256:      hash('sha256', $bytes),
                    pdfBaseName:  $safeName,
                    isPlaceholder: $isPlaceholder,
                );
                $this->extractedCount++;
                $uniqueOnPage++;
            }
        }

        // Clean up temporary file
        unlink($tempPdfPath);

        Log::info('PDF IMPORT COMPLETE', ['count'=>count($candidates), 'paths'=>array_column($candidates, 'stored_path')]);

        return $candidates;
    }

    private function generateFallbackPageImageBytes(): ?string
    {
        // GD extension check
        if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
            Log::error('GD extension not available for fallback image generation');
            return null;
        }

        // Simple placeholder fallback since we can't render PDF pages without external tools
        $w = 600;
        $h = 600;
        $im = imagecreatetruecolor($w, $h);

        if ($im === false) {
            Log::error('Failed to create GD image for fallback');
            return null;
        }

        $bg   = imagecolorallocate($im, 253, 251, 247);
        $dark = imagecolorallocate($im, 100, 100, 100);

        imagefilledrectangle($im, 0, 0, $w - 1, $h - 1, $bg);

        $iconW = 80;
        $iconH = 100;
        $iconX = (int)($w/2) - ($iconW/2);
        $iconY = (int)($h/2) - ($iconH/2) - 20;

        imagefilledrectangle($im, $iconX, $iconY, $iconX + $iconW - 1, $iconY + $iconH - 1, $dark);
        imagefilledellipse($im, (int)($w/2), $iconY + 20, 20, 20, $dark);

        $label = 'Image Coming Soon';
        $labelW = strlen($label) * 8;
        imagestring($im, 3, (int)($w/2) - ($labelW/2), $iconY + $iconH + 10, $label, $dark);

        $brand = 'Svaraa Jewels';
        $brandW = strlen($brand) * 6;
        imagestring($im, 2, (int)($w/2) - ($brandW/2), $iconY + $iconH + 35, $brand, $dark);

        ob_start();
        imagejpeg($im, null, 80);
        $jpeg = (string) ob_get_clean();
        imagedestroy($im);

        if (strlen($jpeg) < 100) {
            Log::error('Generated fallback image too small', ['bytes'=>strlen($jpeg)]);
            return null;
        }

        return $jpeg;
    }

    // ── Helper methods (trimmed for debugging build) ──────────────────────────

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
        // GD extension check
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
            return $bytes;
        }
        $maxDim = 600;
        try {
            $im = @imagecreatefromstring($bytes);
            if ($im === false) {
                return $bytes;
            }
            $w = imagesx($im);
            $h = imagesy($im);
            if ($w > $maxDim || $h > $maxDim) {
                $ratio = min($maxDim / $w, $maxDim / $h);
                $newW  = max(1, (int) round($w * $ratio));
                $newH  = max(1, (int) round($h * $ratio));
                $resized = imagecreatetruecolor($newW, $newH);
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $w, $h);
                imagedestroy($im);
                $im = $resized;
            }
            ob_start();
            imagejpeg($im, null, 75);
            $output = ob_get_clean();
            imagedestroy($im);
            return ($output !== false && $output !== '') ? $output : $bytes;
        } catch (\Throwable) {
            return $bytes;
        }
    }

    private function extractPrice(string $text): string
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

    private function sanitiseFilename(string $name): string
    {
        $safe = preg_replace('/[\s\x00-\x1F\x7F]+/', '-', $name);
        $safe = preg_replace('/[^A-Za-z0-9\-_.]/', '', $safe ?? '');
        $safe = preg_replace('/[-]{2,}/', '-', $safe ?? '');
        $safe = trim($safe ?? '', '-_.');
        return $safe !== '' ? $safe : 'pdf-' . substr(md5($name), 0, 8);
    }

    private function buildCandidate(
        string $storedPath,
        string $name,
        string $price,
        int $pageNumber,
        bool $fromEmbedded,
        string $sha256,
        string $pdfBaseName,
        bool $isPlaceholder,
    ): array {
        return [
            'stored_path'      => $storedPath,
            'preview_url'      => Storage::disk('public')->url($storedPath),
            'name'             => $name,
            'price'            => $price,
            'source'           => 'pdf',
            'pdf_page'         => $pageNumber,
            'pages_found'      => [$pageNumber],
            'from_embedded'    => $fromEmbedded,
            'sha256'           => $sha256,
            'source_pdf'       => $pdfBaseName,
            'duplicate_pages'  => [],
            'occurrences_count'=> 1,
            'is_placeholder'   => $isPlaceholder,
        ];
    }

    private function generateProductName(string $rawText, int $pageNumber): string
    {
        $lines = explode("\n", str_replace(["\r\n", "\r"], "\n", $rawText));

        foreach ($lines as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line));

            if ($line === '') {
                continue;
            }

            if (preg_match('/\b(price|mrp|rs\.?|inr|page|gst|invoice|barcode|qr|sku|hsn|weight|quantity|qty|pdf)\b|₹/i', $line)) {
                continue;
            }

            return mb_substr($line, 0, 80);
        }

        return "Product Page {$pageNumber}";
    }

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
            throw new \RuntimeException("File does not appear to be a valid PDF (detected: {$mime}).");
        }
    }
}