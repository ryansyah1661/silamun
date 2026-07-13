<?php

namespace App\Enums;

enum HealthStatus: string
{
    case SEHAT = 'sehat';                // >= 60%
    case KURANG_SEHAT = 'kurang_sehat';  // 30% - 59.9%
    case MISKIN = 'miskin';              // <= 29.9%

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SEHAT => 'Sehat / Kaya',
            self::KURANG_SEHAT => 'Kurang Sehat',
            self::MISKIN => 'Miskin',
        };
    }

    /**
     * Get CSS color string for badge styling.
     */
    public function color(): string
    {
        return match ($this) {
            self::SEHAT => '#10B981',         // green
            self::KURANG_SEHAT => '#F59E0B',  // amber
            self::MISKIN => '#EF4444',         // red
        };
    }

    /**
     * Get representative emoji.
     */
    public function emoji(): string
    {
        return match ($this) {
            self::SEHAT => '🟢',
            self::KURANG_SEHAT => '🟡',
            self::MISKIN => '🔴',
        };
    }

    /**
     * Determine health status from seagrass coverage percentage
     * based on Kepmen LH No. 200/2004 criteria.
     *
     * - Sehat / Kaya : >= 60%
     * - Kurang Sehat : 30% – 59.9%
     * - Miskin       : <= 29.9%
     */
    public static function fromCoverage(float $percent): self
    {
        return match (true) {
            $percent >= 60.0 => self::SEHAT,
            $percent >= 30.0 => self::KURANG_SEHAT,
            default          => self::MISKIN,
        };
    }
}
