<?php

namespace App\Services;

use App\Models\Region;
use App\Models\Survey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GeoJsonService
{
    /**
     * Convert a collection of surveys to a GeoJSON FeatureCollection.
     *
     * Each feature contains a Point geometry and survey properties.
     *
     * @param Collection<int, Survey> $surveys
     * @return array{type: string, features: array}
     */
    public function surveysToFeatureCollection(Collection $surveys): array
    {
        $features = $surveys
            ->filter(fn (Survey $survey) => $survey->latitude !== null && $survey->longitude !== null)
            ->map(fn (Survey $survey) => $survey->toGeoJsonFeature())
            ->values()
            ->all();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }

    /**
     * Convert a region to a GeoJSON Feature with polygon geometry.
     *
     * Uses PostGIS ST_AsGeoJSON to extract the geometry.
     *
     * @return array{type: string, geometry: mixed, properties: array}
     */
    public function regionToFeature(Region $region): array
    {
        $geometry = DB::selectOne(
            'SELECT ST_AsGeoJSON(geometry) as geojson FROM regions WHERE id = ?',
            [$region->id]
        );

        $geojsonGeometry = $geometry?->geojson
            ? json_decode($geometry->geojson, true)
            : null;

        return [
            'type' => 'Feature',
            'geometry' => $geojsonGeometry,
            'properties' => [
                'id' => $region->id,
                'name' => $region->name,
                'type' => $region->type,
                'code' => $region->code,
                'area_hectares' => $region->area_hectares,
                'survey_count' => $region->surveys()->count(),
            ],
        ];
    }

    /**
     * Get all survey points within a bounding box as a GeoJSON FeatureCollection.
     *
     * Uses PostGIS ST_MakeEnvelope for spatial querying.
     *
     * @param float $north  Northern latitude bound
     * @param float $south  Southern latitude bound
     * @param float $east   Eastern longitude bound
     * @param float $west   Western longitude bound
     * @param array<string, mixed> $filters Additional query filters (status, health_status, region_id)
     * @return array{type: string, features: array, meta: array{total: int}}
     */
    public function getSurveysInBounds(
        float $north,
        float $south,
        float $east,
        float $west,
        array $filters = []
    ): array {
        $query = Survey::query()
            ->withinBounds($north, $south, $east, $west);

        // Apply optional filters
        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (! empty($filters['health_status'])) {
            $query->byHealthStatus($filters['health_status']);
        }

        if (! empty($filters['region_id'])) {
            $query->byRegion($filters['region_id']);
        }

        $surveys = $query->get();

        $featureCollection = $this->surveysToFeatureCollection($surveys);
        $featureCollection['meta'] = [
            'total' => $surveys->count(),
        ];

        return $featureCollection;
    }

    /**
     * Generate choropleth data: average coverage per region.
     *
     * Returns GeoJSON FeatureCollection where each feature is a region
     * with average coverage and health status properties.
     *
     * @return array{type: string, features: array}
     */
    public function getChoroplethData(): array
    {
        $regions = DB::table('regions')
            ->leftJoin('surveys', function ($join) {
                $join->on('regions.id', '=', 'surveys.region_id')
                    ->where('surveys.status', '=', 'published');
            })
            ->select([
                'regions.id',
                'regions.name',
                'regions.type',
                'regions.code',
                'regions.area_hectares',
                DB::raw('AVG(surveys.total_coverage_percent) as avg_coverage'),
                DB::raw('COUNT(surveys.id) as survey_count'),
            ])
            ->groupBy('regions.id', 'regions.name', 'regions.type', 'regions.code', 'regions.area_hectares')
            ->having(DB::raw('COUNT(surveys.id)'), '>', 0)
            ->get();

        $features = $regions->map(function ($region) {
            // Fetch geometry via PostGIS
            $geometry = DB::selectOne(
                'SELECT ST_AsGeoJSON(geometry) as geojson FROM regions WHERE id = ?',
                [$region->id]
            );

            $geojsonGeometry = $geometry?->geojson
                ? json_decode($geometry->geojson, true)
                : null;

            $avgCoverage = round((float) $region->avg_coverage, 2);
            $healthStatus = \App\Enums\HealthStatus::fromCoverage($avgCoverage);

            return [
                'type' => 'Feature',
                'geometry' => $geojsonGeometry,
                'properties' => [
                    'id' => $region->id,
                    'name' => $region->name,
                    'type' => $region->type,
                    'code' => $region->code,
                    'area_hectares' => $region->area_hectares,
                    'avg_coverage' => $avgCoverage,
                    'survey_count' => (int) $region->survey_count,
                    'health_status' => $healthStatus->value,
                    'health_status_label' => $healthStatus->label(),
                    'health_status_color' => $healthStatus->color(),
                ],
            ];
        })->all();

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
        ];
    }
}
