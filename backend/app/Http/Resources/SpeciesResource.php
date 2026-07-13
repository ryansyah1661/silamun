<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Species model.
 *
 * Transforms seagrass species data with taxonomy details,
 * conservation status, and carbon sequestration metrics.
 */
class SpeciesResource extends JsonResource
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
            'name' => $this->name,
            'local_name' => $this->local_name,
            'family' => $this->family,
            'order' => $this->order,
            'slug' => $this->slug,
            'description' => $this->description,
            'morphology' => $this->morphology,
            'habitat' => $this->habitat,
            'depth_range' => $this->depth_range,
            'iucn_status' => $this->iucn_status,
            'carbon_factor' => $this->carbon_factor ? (float) $this->carbon_factor : null,
            'photo_url' => $this->photo_url,
            'surveys_count' => $this->whenCounted('surveys', $this->surveys_count ?? 0),
        ];
    }
}
