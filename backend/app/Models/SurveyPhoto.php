<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SurveyPhoto extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'survey_id',
        'photo_path',
        'photo_url',
        'caption',
        'photo_type',
        'sort_order',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * @var array<int, string>
     */
    protected $appends = [
        'full_url',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    // ──────────────────────────────────────────────
    // Accessors
    // ──────────────────────────────────────────────

    /**
     * Return the photo_url if set, otherwise generate a URL from photo_path via Storage.
     */
    public function getFullUrlAttribute(): ?string
    {
        if (! empty($this->photo_url)) {
            return $this->photo_url;
        }

        if (! empty($this->photo_path)) {
            return Storage::url($this->photo_path);
        }

        return null;
    }
}
