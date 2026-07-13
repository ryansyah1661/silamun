<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Survey model.
 *
 * Transforms survey data into a consistent JSON structure
 * with health status metadata, species counts, and conditional relations.
 */
class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'location_name' => $this->location_name,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'survey_date' => $this->survey_date?->format('Y-m-d'),
            'sampling_method' => $this->sampling_method,

            // Environmental parameters
            'water_temperature' => $this->water_temperature ? (float) $this->water_temperature : null,
            'salinity' => $this->salinity ? (float) $this->salinity : null,
            'water_depth' => $this->water_depth ? (float) $this->water_depth : null,
            'substrate_type' => $this->substrate_type,

            // Coverage & Health
            'total_coverage_percent' => (float) $this->total_coverage_percent,
            'health_status' => $this->health_status ? [
                'value' => $this->health_status->value,
                'label' => $this->health_status->label(),
                'color' => $this->health_status->color(),
                'emoji' => $this->health_status->emoji(),
            ] : null,

            // Workflow status
            'status' => $this->status ? [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ] : null,

            // Aggregated counts
            'species_count' => $this->whenCounted('species', $this->species_count ?? $this->species?->count()),
            'dominant_species' => $this->dominant_species,
            'photos_count' => $this->whenCounted('photos', $this->photos_count ?? $this->photos?->count()),

            // Relations metadata
            'surveyor' => $this->whenLoaded('surveyor', fn () => [
                'id' => $this->surveyor->id,
                'name' => $this->surveyor->name,
            ]),
            'region' => $this->whenLoaded('region', fn () => [
                'id' => $this->region->id,
                'name' => $this->region->name,
                'type' => $this->region->type,
            ]),

            // Validation info
            'verified_by' => $this->whenLoaded('verifiedBy', fn () => [
                'id' => $this->verifiedBy->id,
                'name' => $this->verifiedBy->name,
            ]),
            'verified_at' => $this->verified_at?->toIso8601String(),

            'notes' => $this->notes,

            // Conditional relations (loaded when explicitly requested)
            'species' => $this->whenLoaded('species', fn () => $this->species->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'local_name' => $s->local_name,
                'coverage_percent' => (float) ($s->pivot->coverage_percent ?? 0),
                'density' => $s->pivot->density ?? null,
                'frequency' => $s->pivot->frequency ?? null,
            ])),
            'photos' => $this->whenLoaded('photos', fn () => $this->photos->map(fn ($p) => [
                'id' => $p->id,
                'url' => $p->url,
                'thumbnail_url' => $p->thumbnail_url,
                'caption' => $p->caption,
                'type' => $p->type,
            ])),
            'validations' => $this->whenLoaded('validations', fn () => $this->validations->map(fn ($v) => [
                'id' => $v->id,
                'action' => $v->action,
                'comments' => $v->comments,
                'validator' => $v->validator?->name,
                'created_at' => $v->created_at?->toIso8601String(),
            ])),

            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
