<?php

namespace App\Http\Controllers\Api;

use App\Enums\HealthStatus;
use App\Enums\SurveyStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SurveyResource;
use App\Models\Region;
use App\Models\Species;
use App\Models\Survey;
use App\Services\CarbonCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller for public dashboard statistics.
 *
 * Provides aggregate data for the public-facing dashboard
 * including summary stats, trends, comparisons, and recent surveys.
 * No authentication required.
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly CarbonCalculatorService $carbonService,
    ) {}

    /**
     * Return summary statistics for the public dashboard.
     *
     * Includes: total area, total surveys, species count, avg coverage,
     * health distribution, and carbon total.
     */
    public function summary(): JsonResponse
    {
        try {
            $publishedSurveys = Survey::where('status', SurveyStatus::PUBLISHED);

            $totalSurveys = $publishedSurveys->count();
            $speciesCount = Species::count();
            $avgCoverage = round($publishedSurveys->avg('total_coverage_percent') ?? 0, 2);

            // Health status distribution
            $healthDistribution = Survey::where('status', SurveyStatus::PUBLISHED)
                ->select('health_status', DB::raw('COUNT(*) as count'))
                ->groupBy('health_status')
                ->get()
                ->map(fn ($item) => [
                    'status' => $item->health_status?->value,
                    'label' => $item->health_status?->label(),
                    'color' => $item->health_status?->color(),
                    'emoji' => $item->health_status?->emoji(),
                    'count' => $item->count,
                ]);

            // Carbon estimate
            $carbonTotal = $this->carbonService->getNationalTotal();

            // Total surveyed area (estimated from regions)
            $totalArea = Region::sum('area_hectares') ?? 0;

            // Provinces with surveys count
            $provincesWithSurveys = Region::where('type', 'provinsi')
                ->whereHas('surveys')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_area_hectares' => (float) $totalArea,
                    'total_surveys' => $totalSurveys,
                    'species_count' => $speciesCount,
                    'avg_coverage_percent' => $avgCoverage,
                    'health_distribution' => $healthDistribution,
                    'carbon_total_tons' => $carbonTotal,
                    'provinces_with_surveys' => $provincesWithSurveys,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan dashboard.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return yearly trend data for charts.
     *
     * Accepts optional region_id filter.
     */
    public function trends(Request $request): JsonResponse
    {
        try {
            $query = Survey::where('status', SurveyStatus::PUBLISHED);

            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            $trends = $query
                ->select(
                    DB::raw("EXTRACT(YEAR FROM survey_date) as year"),
                    DB::raw('COUNT(*) as surveys_count'),
                    DB::raw('AVG(total_coverage_percent) as avg_coverage'),
                    DB::raw('MIN(total_coverage_percent) as min_coverage'),
                    DB::raw('MAX(total_coverage_percent) as max_coverage')
                )
                ->groupBy(DB::raw("EXTRACT(YEAR FROM survey_date)"))
                ->orderBy('year')
                ->get()
                ->map(fn ($item) => [
                    'year' => (int) $item->year,
                    'surveys_count' => $item->surveys_count,
                    'avg_coverage' => round($item->avg_coverage, 2),
                    'min_coverage' => round($item->min_coverage, 2),
                    'max_coverage' => round($item->max_coverage, 2),
                ]);

            return response()->json([
                'success' => true,
                'data' => $trends,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data tren.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return comparison data between regions (top 10 by survey count).
     */
    public function comparison(Request $request): JsonResponse
    {
        try {
            $regions = Region::where('type', $request->get('type', 'provinsi'))
                ->withCount(['surveys' => function ($q) {
                    $q->where('status', SurveyStatus::PUBLISHED);
                }])
                ->withAvg(['surveys' => function ($q) {
                    $q->where('status', SurveyStatus::PUBLISHED);
                }], 'total_coverage_percent')
                ->having('surveys_count', '>', 0)
                ->orderByDesc('surveys_count')
                ->limit(10)
                ->get()
                ->map(fn ($region) => [
                    'region_id' => $region->id,
                    'region_name' => $region->name,
                    'region_code' => $region->code,
                    'surveys_count' => $region->surveys_count,
                    'avg_coverage' => round($region->surveys_avg_total_coverage_percent ?? 0, 2),
                ]);

            return response()->json([
                'success' => true,
                'data' => $regions,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data perbandingan wilayah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Return 5 most recent published surveys.
     */
    public function recentSurveys(): JsonResponse
    {
        try {
            $surveys = Survey::where('status', SurveyStatus::PUBLISHED)
                ->with(['surveyor', 'region'])
                ->withCount(['species', 'photos'])
                ->orderByDesc('survey_date')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'data' => SurveyResource::collection($surveys),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat survei terbaru.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
