<?php

namespace App\Support\Locks;

use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Cache\Lock;

class BatchLockManager
{
    const LOCK_PREFIX = 'bulk-upload:';
    const LOCK_TTL = 300; // 5 minutes

    public static function acquire(string $batchUuid): bool
    {
        $lock = Cache::lock(self::LOCK_PREFIX . $batchUuid, self::LOCK_TTL);

        return $lock->get();
    }

    public static function release(string $batchUuid): void
    {
        $lock = Cache::lock(self::LOCK_PREFIX . $batchUuid, self::LOCK_TTL);
        $lock->release();
    }

    public static function forceRelease(string $batchUuid): void
    {
        Cache::forget(self::LOCK_PREFIX . $batchUuid);
    }
}
