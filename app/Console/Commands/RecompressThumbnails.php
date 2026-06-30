<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Re-compress existing oversized thumbnails in storage/app/public/products.
 *
 * Usage:
 *   php artisan thumbnails:recompress
 *   php artisan thumbnails:recompress --dir=products        (images uploaded via form)
 *   php artisan thumbnails:recompress --dry-run             (preview only, no writes)
 *   php artisan thumbnails:recompress --quality=60          (override JPEG quality)
 *
 * Safe to run multiple times — files already under MIN_BYTES_SKIP are skipped.
 */
class RecompressThumbnails extends Command
{
    protected $signature = 'thumbnails:recompress
        {--dir=products : Subdirectory inside the public disk to scan}
        {--max-dim=1200      : Maximum pixel dimension (width or height)}
        {--quality=75        : JPEG/WebP output quality (1-100)}
        {--dry-run           : Show what would happen without writing anything}';

    protected $description = 'Batch re-compress oversized product thumbnails stored in the public disk.';

    private const MIN_BYTES_SKIP = 200_000; // Skip files already <= 200 KB

    public function handle(): int
    {
        $dir     = ltrim((string) $this->option('dir'), '/');
        $maxDim  = (int) $this->option('max-dim');
        $quality = (int) $this->option('quality');
        $dryRun  = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY-RUN mode -- no files will be written.');
        }

        $disk  = Storage::disk('public');
        $files = $disk->files($dir);

        if (empty($files)) {
            $this->error("No files found in public disk under: {$dir}");
            return self::FAILURE;
        }

        $this->info(sprintf('Scanning %d file(s) in "%s" ...', count($files), $dir));

        $processed  = 0;
        $skipped    = 0;
        $failed     = 0;
        $savedBytes = 0;

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        foreach ($files as $relativePath) {
            $bar->advance();

            $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                $skipped++;
                continue;
            }

            $absPath  = $disk->path($relativePath);
            $origSize = filesize($absPath);

            if ($origSize <= self::MIN_BYTES_SKIP) {
                $skipped++;
                continue; // Already small enough
            }

            $bytes = file_get_contents($absPath);
            if ($bytes === false) {
                $this->newLine();
                $this->warn("  Cannot read: {$relativePath}");
                $failed++;
                continue;
            }

            $gdExt = ($ext === 'jpg') ? 'jpeg' : $ext;
            $result = $this->recompress($bytes, $gdExt, $maxDim, $quality);

            if ($result === null) {
                $this->newLine();
                $this->warn("  Skipped (GD could not decode): {$relativePath}");
                $failed++;
                continue;
            }

            $newSize   = strlen($result);
            $reduction = $origSize - $newSize;

            if ($reduction <= 0) {
                // Re-encode made it larger or same -- keep original
                $skipped++;
                continue;
            }

            $savedBytes += $reduction;

            if (! $dryRun) {
                file_put_contents($absPath, $result);
                clearstatcache(true, $absPath);
            }

            $processed++;
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Value'],
            [
                ['Files processed (re-encoded)',          $processed],
                ['Files skipped (small / non-image)',     $skipped],
                ['Files failed (unreadable / GD error)', $failed],
                ['Disk space saved',                      $this->formatBytes($savedBytes)],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY-RUN: no changes were written to disk.');
        } else {
            $this->info('Done. Run php artisan thumbnails:recompress --dry-run to preview again.');
        }

        return self::SUCCESS;
    }

    private function recompress(string $bytes, string $ext, int $maxDim, int $quality): ?string
    {
        try {
            $im = @imagecreatefromstring($bytes);
            if ($im === false) {
                return null;
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
            match ($ext) {
                'jpeg'  => imagejpeg($im, null, $quality),
                'png'   => imagepng($im, null, 8),
                'webp'  => imagewebp($im, null, $quality),
                default => null,
            };
            $output = ob_get_clean();
            imagedestroy($im);

            return ($output !== false && $output !== '') ? $output : null;

        } catch (\Throwable) {
            return null;
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        if ($bytes >= 1_024) {
            return round($bytes / 1_024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
