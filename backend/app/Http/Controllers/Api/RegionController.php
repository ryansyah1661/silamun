<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegionResource;
use App\Models\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Controller for managing Indonesian administrative regions.
 *
 * Provides listing, filtering, and hierarchical region data
 * for provinces (provinsi) and districts (kabupaten).
 */
class RegionController extends Controller
{
    /**
     * List regions with optional type and search filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Region::query()
                ->withCount(['surveys', 'children']);

            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('search')) {
                $query->where('name', 'ILIKE', "%{$request->search}%");
            }

            if ($request->filled('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            $regions = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => RegionResource::collection($regions),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data wilayah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a single region with children and survey statistics.
     */
    public function show(Region $region): JsonResponse
    {
        try {
            $region->load(['parent', 'children' => function ($q) {
                $q->withCount('surveys')->orderBy('name');
            }]);
            $region->loadCount(['surveys', 'children']);

            // Calculate additional stats
            $stats = [
                'total_surveys' => $region->surveys_count,
                'avg_coverage' => round($region->surveys()->avg('total_coverage_percent') ?? 0, 2),
                'species_found' => $region->surveys()
                    ->join('survey_species', 'surveys.id', '=', 'survey_species.survey_id')
                    ->distinct('survey_species.species_id')
                    ->count('survey_species.species_id'),
            ];

            return response()->json([
                'success' => true,
                'data' => new RegionResource($region),
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail wilayah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * List only provinces (provinsi).
     */
    public function provinces(): JsonResponse
    {
        try {
            $provinces = Region::where('type', 'provinsi')
                ->withCount(['surveys', 'children'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => RegionResource::collection($provinces),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data provinsi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * List kabupaten/kota, optionally filtered by province_id.
     */
    public function kabupaten(Request $request): JsonResponse
    {
        try {
            $query = Region::where('type', 'kabupaten')
                ->withCount('surveys');

            if ($request->filled('province_id')) {
                $query->where('parent_id', $request->province_id);
            }

            $kabupaten = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => RegionResource::collection($kabupaten),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data kabupaten.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
