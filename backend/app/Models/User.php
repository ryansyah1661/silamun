<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'institution',
        'region_id',
        'is_active',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class,
        'is_active' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'role_label',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    public function assignedRegion(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    public function validations(): HasMany
    {
        return $this->hasMany(SurveyValidation::class, 'validator_id');
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeSurveyors($query)
    {
        return $query->where('role', UserRole::SURVEYOR);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeVerifikators($query)
    {
        return $query->where('role', UserRole::VERIFIKATOR);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', UserRole::SUPER_ADMIN);
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isVerifikator(): bool
    {
        return $this->role === UserRole::VERIFIKATOR;
    }

    public function isSurveyor(): bool
    {
        return $this->role === UserRole::SURVEYOR;
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get the human-readable role label.
     */
    public function getRoleLabelAttribute(): string
    {
        return $this->role?->label() ?? '-';
    }
}
