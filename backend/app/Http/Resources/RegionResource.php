<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Region model.
 *
 * Transforms region data with parent relationships,
 * survey counts, and children counts.
 */
class RegionResource extends JsonResource
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
            'type' => $this->type,
            'code' => $this->code,
            'parent_id' => $this->parent_id,
            'parent_name' => $this->whenLoaded('parent', fn () => $this->parent?->name),
            'area_hectares' => $this->area_hectares ? (float) $this->area_hectares : null,
            'surveys_count' => $this->whenCounted('surveys', $this->surveys_count ?? 0),
            'children_count' => $this->whenCounted('children', $this->children_count ?? 0),
            'children' => $this->whenLoaded('children', fn () => RegionResource::collection($this->children)),
        ];
    }
}
