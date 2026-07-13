<?php

namespace App\Http\Controllers\Api;

use App\Enums\SurveyStatus;
use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Survey;
use App\Services\CarbonCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller for carbon sequestration calculations.
 *
 * Provides national/regional carbon summaries,
 * custom carbon calculators, and trend data.
 */
class CarbonController extends Controller
{
    public function __construct(
        private readonly CarbonCalculatorService $carbonService,
    ) {}

    /**
     * National carbon summary.
     *
     * Returns total carbon stock, absorption rate, and economic value
     * estimated from all published survey data.
     */
    public function national(): JsonResponse
    {
        try {
            $summary = $this->carbonService->getNationalSummary();

            return response()->json([
                'success' => true,
                'data' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat ringkasan karbon nasional.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Carbon calculation for a specific region.
     */
    public function byRegion(Request $request): JsonResponse
    {
        $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
        ]);

        try {
            $region = Region::findOrFail($request->region_id);
            $result = $this->carbonService->calculateForRegion($region);

            return response()->json([
                'success' => true,
                'data' => [
                    'region' => [
                        'id' => $region->id,
                        'name' => $region->name,
                    ],
                    ...$result,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung karbon untuk wilayah.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Custom carbon calculator.
     *
     * Accepts area_ha and coverage_percent, returns estimated carbon data.
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'area_ha' => ['required', 'numeric', 'min:0.01', 'max:1000000'],
            'coverage_percent' => ['required', 'numeric', 'between:0,100'],
            'species_id' => ['nullable', 'exists:species,id'],
        ]);

        try {
            $result = $this->carbonService->customCalculation(
                areaHa: $validated['area_ha'],
                coveragePercent: $validated['coverage_percent'],
                speciesId: $validated['species_id'] ?? null,
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghitung estimasi karbon.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Yearly carbon accumulation trend.
     *
     * Returns estimated carbon values aggregated by year.
     */
    public function trend(Request $request): JsonResponse
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
                    DB::raw('SUM(total_coverage_percent) as sum_coverage')
                )
                ->groupBy(DB::raw("EXTRACT(YEAR FROM survey_date)"))
                ->orderBy('year')
                ->get()
                ->map(function ($item) {
                    $estimatedCarbon = $this->carbonService->estimateFromCoverage(
                        $item->avg_coverage,
                        $item->surveys_count
                    );

                    return [
                        'year' => (int) $item->year,
                        'surveys_count' => $item->surveys_count,
                        'avg_coverage' => round($item->avg_coverage, 2),
                        'estimated_carbon_tons' => round($estimatedCarbon, 2),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $trends,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat tren karbon.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
