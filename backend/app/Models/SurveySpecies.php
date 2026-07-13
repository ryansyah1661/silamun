<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SurveySpecies extends Pivot
{
    /**
     * Indicates if the model's ID is auto-incrementing.
     */
    public $incrementing = true;

    /**
     * The table associated with the model.
     */
    protected $table = 'survey_species';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'species_id',
        'coverage_percent',
        'density',
        'is_dominant',
        'notes',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'coverage_percent' => 'float',
        'density' => 'float',
        'is_dominant' => 'boolean',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(Species::class);
    }
}
