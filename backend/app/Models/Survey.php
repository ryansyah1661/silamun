<?php

namespace App\Models;

use App\Enums\HealthStatus;
use App\Enums\SurveyStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Survey extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Columns that are mass-assignable.
     * Note: code, health_status, carbon_stock_estimation are auto-calculated.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'region_id',
        'location_name',
        'latitude',
        'longitude',
        'survey_date',
        'sampling_method',
        'water_temperature',
        'salinity',
        'water_depth',
        'substrate_type',
        'total_coverage_percent',
        'notes',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'survey_date' => 'date',
        'status' => SurveyStatus::class,
        'health_status' => HealthStatus::class,
        'verified_at' => 'datetime',
        'latitude' => 'float',
        'longitude' => 'float',
        'water_depth' => 'float',
        'water_temperature' => 'float',
        'salinity' => 'float',
        'total_coverage_percent' => 'float',
        'carbon_stock_estimation' => 'float',
    ];

    /**
     * Relationships to eager load by default.
     *
     * @var array<int, string>
     */
    protected $with = ['user', 'region'];

    // ──────────────────────────────────────────────
    // Boot
    // ──────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (Survey $survey) {
            // Auto-generate unique survey code: SRV-YYYY-NNNNN
            if (empty($survey->code)) {
                $year = now()->year;
                $lastNumber = static::query()
                    ->whereYear('created_at', $year)
                    ->withoutGlobalScopes()
                    ->count();
                $survey->code = sprintf('SRV-%d-%05d', $year, $lastNumber + 1);
            }

            // Auto-calculate health status from coverage
            if ($survey->total_coverage_percent !== null) {
                $survey->health_status = HealthStatus::fromCoverage($survey->total_coverage_percent);
            }

            // Auto-generate PostGIS point from lat/lng
            if ($survey->latitude !== null && $survey->longitude !== null) {
                $survey->location_point = DB::raw(
                    "ST_SetSRID(ST_MakePoint({$survey->longitude}, {$survey->latitude}), 4326)"
                );
            }

            // Default status
            if ($survey->status === null) {
                $survey->status = SurveyStatus::DRAFT;
            }
        });

        static::updating(function (Survey $survey) {
            // Recalculate health status when coverage changes
            if ($survey->isDirty('total_coverage_percent') && $survey->total_coverage_percent !== null) {
                $survey->health_status = HealthStatus::fromCoverage($survey->total_coverage_percent);
            }

            // Recalculate PostGIS point if coordinates change
            if ($survey->isDirty(['latitude', 'longitude'])
                && $survey->latitude !== null
                && $survey->longitude !== null
            ) {
                $survey->location_point = DB::raw(
                    "ST_SetSRID(ST_MakePoint({$survey->longitude}, {$survey->latitude}), 4326)"
                );
            }
        });
    }

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * The verifier/validator who approved or rejected this survey.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Species observed in this survey with pivot data.
     */
    public function species(): BelongsToMany
    {
        return $this->belongsToMany(Species::class, 'survey_species')
            ->using(SurveySpecies::class)
            ->withPivot(['coverage_percent', 'density', 'is_dominant', 'notes'])
            ->withTimestamps();
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SurveyPhoto::class);
    }

    public function validations(): HasMany
    {
        return $this->hasMany(SurveyValidation::class);
    }

    // ──────────────────────────────────────────────
    // Custom Relationship-Like Methods
    // ──────────────────────────────────────────────

    /**
     * Get the species with the highest coverage percentage in this survey.
     */
    public function dominantSpecies(): ?Species
    {
        return $this->species()
            ->orderByPivot('coverage_percent', 'desc')
            ->first();
    }

    // ──────────────────────────────────────────────
    // Scopes
    // ──────────────────────────────────────────────

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', SurveyStatus::PUBLISHED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', SurveyStatus::PENDING);
    }

    public function scopeByStatus(Builder $query, SurveyStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeByHealthStatus(Builder $query, HealthStatus $status): Builder
    {
        return $query->where('health_status', $status);
    }

    /**
     * @param string|\Carbon\Carbon $from
     * @param string|\Carbon\Carbon $to
     */
    public function scopeByDateRange(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('survey_date', [$from, $to]);
    }

    /**
     * Spatial query: find surveys within a bounding box using PostGIS.
     */
    public function scopeWithinBounds(
        Builder $query,
        float $north,
        float $south,
        float $east,
        float $west
    ): Builder {
        return $query->whereRaw(
            'ST_Within(location_point, ST_MakeEnvelope(?, ?, ?, ?, 4326))',
            [$west, $south, $east, $north]
        );
    }

    // ──────────────────────────────────────────────
    // Methods
    // ──────────────────────────────────────────────

    /**
     * Convert this survey to a GeoJSON Feature array.
     *
     * @return array<string, mixed>
     */
    public function toGeoJsonFeature(): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [$this->longitude, $this->latitude],
            ],
            'properties' => [
                'id' => $this->id,
                'code' => $this->code,
                'location_name' => $this->location_name,
                'survey_date' => $this->survey_date?->toDateString(),
                'total_coverage_percent' => $this->total_coverage_percent,
                'health_status' => $this->health_status?->value,
                'health_status_label' => $this->health_status?->label(),
                'health_status_color' => $this->health_status?->color(),
                'species_count' => $this->species()->count(),
                'status' => $this->status?->value,
                'region' => $this->region?->name,
                'surveyor' => $this->user?->name,
            ],
        ];
    }

    /**
     * Approve/publish this survey.
     */
    public function approve(User $verifier): bool
    {
        $this->status = SurveyStatus::PUBLISHED;
        $this->verified_by = $verifier->id;
        $this->verified_at = now();

        $saved = $this->save();

        if ($saved) {
            $this->validations()->create([
                'validator_id' => $verifier->id,
                'action' => 'approved',
                'comments' => null,
            ]);
        }

        return $saved;
    }

    /**
     * Reject this survey with a reason.
     */
    public function reject(User $verifier, string $reason): bool
    {
        $this->status = SurveyStatus::REJECTED;
        $this->verified_by = $verifier->id;
        $this->verified_at = now();
        $this->rejection_reason = $reason;

        $saved = $this->save();

        if ($saved) {
            $this->validations()->create([
                'validator_id' => $verifier->id,
                'action' => 'rejected',
                'comments' => $reason,
            ]);
        }

        return $saved;
    }
}
