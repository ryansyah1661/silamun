<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for Survey model in GeoJSON Feature format.
 *
 * Transforms survey data into a GeoJSON Feature object
 * for use with map libraries (Leaflet, Mapbox, etc.).
 */
class SurveyGeoJsonResource extends JsonResource
{
    /**
     * Transform the resource into a GeoJSON Feature array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => 'Feature',
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [
                    (float) $this->longitude,
                    (float) $this->latitude,
                ],
            ],
            'properties' => [
                'id' => $this->id,
                'code' => $this->code,
                'location_name' => $this->location_name,
                'coverage' => (float) $this->total_coverage_percent,
                'health_status' => $this->health_status ? [
                    'value' => $this->health_status->value,
                    'label' => $this->health_status->label(),
                    'color' => $this->health_status->color(),
                    'emoji' => $this->health_status->emoji(),
                ] : null,
                'status' => $this->status?->value,
                'species_count' => $this->species_count ?? $this->species?->count() ?? 0,
                'survey_date' => $this->survey_date?->format('Y-m-d'),
                'photo_thumbnail' => $this->photos?->first()?->thumbnail_url,
                'region_name' => $this->region?->name,
            ],
        ];
    }

    /**
     * Create a GeoJSON FeatureCollection from a collection of surveys.
     *
     * @param  \Illuminate\Http\Resources\Json\AnonymousResourceCollection  $collection
     * @return array<string, mixed>
     */
    public static function featureCollection($surveys): array
    {
        return [
            'type' => 'FeatureCollection',
            'features' => self::collection($surveys)->resolve(),
        ];
    }
}
