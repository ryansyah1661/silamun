<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Species extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'local_name',
        'family',
        'genus',
        'description',
        'characteristics',
        'habitat',
        'min_depth',
        'max_depth',
        'distribution',
        'image_url',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'min_depth' => 'float',
        'max_depth' => 'float',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'depth_range',
    ];

    // ──────────────────────────────────────────────
    // Boot
    // ──────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Species $species) {
            if (empty($species->slug)) {
                $species->slug = Str::slug($species->name);
            }
        });
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * Surveys that recorded this species.
     */
    public function surveys(): BelongsToMany
    {
        return $this->belongsToMany(Survey::class, 'survey_species')
            ->withPivot(['coverage_percent', 'density', 'is_dominant', 'notes'])
            ->withTimestamps();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $family
     */
    public function scopeByFamily($query, string $family)
    {
        return $query->where('family', $family);
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Get a formatted depth range string, e.g. "0.5 - 6 m".
     */
    public function getDepthRangeAttribute(): ?string
    {
        if ($this->min_depth === null && $this->max_depth === null) {
            return null;
        }

        $min = $this->min_depth ?? 0;
        $max = $this->max_depth ?? $min;

        return "{$min} - {$max} m";
    }
}
