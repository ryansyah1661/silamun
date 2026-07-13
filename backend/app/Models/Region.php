<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Note: geometry column is handled via raw PostGIS queries, not in $fillable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'code',
        'parent_id',
        'area_hectares',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'area_hectares' => 'float',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Self-referencing parent region.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Self-referencing child regions.
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Surveys conducted within this region.
     */
    public function surveys(): HasMany
    {
        return $this->hasMany(Survey::class);
    }

    /**
     * Users assigned to this region.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeProvinces($query)
    {
        return $query->where('type', 'provinsi');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeKabupaten($query)
    {
        return $query->where('type', 'kabupaten');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
