<?php

namespace App\Http\Controllers\Api;

use App\Enums\SurveyStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SurveyGeoJsonResource;
use App\Models\Region;
use App\Models\Survey;
use App\Services\GeoJsonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for map-related API endpoints.
 *
 * Provides GeoJSON data for survey points, region boundaries,
 * bounding box queries, and choropleth visualizations.
 */
class MapController extends Controller
{
    public function __construct(
        private readonly GeoJsonService $geoJsonService,
    ) {}

    /**
     * Return ALL published surveys as a GeoJSON FeatureCollection.
     *
     * Accepts filters: region_id, health_status, species_id, year
     */
    public function surveyPoints(Request $request): JsonResponse
    {
        try {
            $query = Survey::query()
                ->where('status', SurveyStatus::PUBLISHED)
                ->with(['region', 'photos'])
                ->withCount('species');

            // Apply filters
            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            if ($request->filled('health_status')) {
                $query->where('health_status', $request->health_status);
            }

            if ($request->filled('species_id')) {
                $query->whereHas('species', function ($q) use ($request) {
                    $q->where('species.id', $request->species_id);
                });
            }

            if ($request->filled('year')) {
                $query->whereYear('survey_date', $request->year);
            }

            $surveys = $query->get();

            return response()->json(
                SurveyGeoJsonResource::featureCollection($surveys)
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data peta survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return region polygons as GeoJSON.
     *
     * Accepts type filter: provinsi, kabupaten
     */
    public function regionBoundaries(Request $request): JsonResponse
    {
        try {
            $query = Region::query();

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            $regions = $query->get();

            $geojson = $this->geoJsonService->regionsToGeoJson($regions);

            return response()->json($geojson);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat batas wilayah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return surveys within a geographic bounding box.
     *
     * Required params: north, south, east, west (latitude/longitude bounds)
     */
    public function boundingBox(Request $request): JsonResponse
    {
        $request->validate([
            'north' => ['required', 'numeric', 'between:-90,90'],
            'south' => ['required', 'numeric', 'between:-90,90'],
            'east' => ['required', 'numeric', 'between:-180,180'],
            'west' => ['required', 'numeric', 'between:-180,180'],
        ]);

        try {
            $surveys = Survey::query()
                ->where('status', SurveyStatus::PUBLISHED)
                ->whereBetween('latitude', [$request->south, $request->north])
                ->whereBetween('longitude', [$request->west, $request->east])
                ->with(['region', 'photos'])
                ->withCount('species')
                ->get();

            return response()->json(
                SurveyGeoJsonResource::featureCollection($surveys)
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data bounding box.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return choropleth data — average coverage per region.
     *
     * Used for thematic map coloring based on seagrass health per region.
     */
    public function choropleth(Request $request): JsonResponse
    {
        try {
            $query = Region::query()
                ->where('type', $request->get('type', 'provinsi'))
                ->withCount('surveys')
                ->withAvg('surveys', 'total_coverage_percent');

            $regions = $query->get()->map(function ($region) {
                $avgCoverage = round($region->surveys_avg_total_coverage_percent ?? 0, 2);

                return [
                    'region_id' => $region->id,
                    'region_name' => $region->name,
                    'region_code' => $region->code,
                    'surveys_count' => $region->surveys_count,
                    'avg_coverage' => $avgCoverage,
                    'health_category' => $this->getCoverageCategory($avgCoverage),
                    'color' => $this->getCoverageColor($avgCoverage),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $regions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data choropleth.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Determine health category based on coverage percentage.
     */
    private function getCoverageCategory(float $coverage): string
    {
        return match (true) {
            $coverage >= 60 => 'sehat',
            $coverage >= 30 => 'kurang_sehat',
            $coverage > 0 => 'rusak',
            default => 'tidak_ada_data',
        };
    }

    /**
     * Determine choropleth color based on coverage percentage.
     */
    private function getCoverageColor(float $coverage): string
    {
        return match (true) {
            $coverage >= 60 => '#22c55e', // green
            $coverage >= 30 => '#f59e0b', // amber
            $coverage > 0 => '#ef4444',   // red
            default => '#9ca3af',          // gray
        };
    }
}
