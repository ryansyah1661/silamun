<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for User model.
 *
 * Transforms user data with role metadata,
 * institution details, and survey counts.
 */
class UserResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role ? [
                'value' => $this->role->value,
                'label' => $this->role->label(),
            ] : null,
            'institution' => $this->institution,
            'phone' => $this->phone,
            'assigned_region' => $this->whenLoaded('assignedRegion', fn () => [
                'id' => $this->assignedRegion->id,
                'name' => $this->assignedRegion->name,
            ]),
            'is_active' => (bool) $this->is_active,
            'surveys_count' => $this->whenCounted('surveys', $this->surveys_count ?? 0),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
