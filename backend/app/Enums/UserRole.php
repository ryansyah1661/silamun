<?php

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case VERIFIKATOR = 'verifikator';
    case SURVEYOR = 'surveyor';

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrator',
            self::VERIFIKATOR => 'Verifikator',
            self::SURVEYOR => 'Surveyor',
        };
    }
}
