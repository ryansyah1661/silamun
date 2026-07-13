<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case PUBLISHED = 'published';

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draf',
            self::PENDING => 'Menunggu Verifikasi',
            self::APPROVED => 'Disetujui',
            self::REJECTED => 'Ditolak',
            self::PUBLISHED => 'Dipublikasikan',
        };
    }

    /**
     * Get CSS color string for badge styling.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => '#6B7280',      // gray
            self::PENDING => '#F59E0B',    // amber
            self::APPROVED => '#10B981',   // green
            self::REJECTED => '#EF4444',   // red
            self::PUBLISHED => '#3B82F6',  // blue
        };
    }

    /**
     * Determine if a survey with this status can still be edited.
     */
    public function isEditable(): bool
    {
        return match ($this) {
            self::DRAFT, self::REJECTED => true,
            self::PENDING, self::APPROVED, self::PUBLISHED => false,
        };
    }
}
