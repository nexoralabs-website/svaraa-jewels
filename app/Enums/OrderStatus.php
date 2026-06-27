<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case PAID = 'paid';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case COMPLETED = 'completed';

    public function canTransitionTo(self $newStatus): bool
    {
        return match ($this) {
            self::PENDING => in_array($newStatus, [self::PAID, self::CANCELLED, self::FAILED]),
            self::PAID => in_array($newStatus, [self::PROCESSING, self::CANCELLED, self::REFUNDED, self::FAILED]),
            self::PROCESSING => in_array($newStatus, [self::SHIPPED, self::CANCELLED, self::REFUNDED]),
            self::SHIPPED => in_array($newStatus, [self::DELIVERED, self::COMPLETED, self::CANCELLED, self::REFUNDED]),
            self::DELIVERED => in_array($newStatus, [self::COMPLETED, self::CANCELLED, self::REFUNDED]),
            default => false,
        };
    }
}
