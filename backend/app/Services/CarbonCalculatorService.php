<?php

namespace App\Services;

use App\Models\Region;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;

class CarbonCalculatorService
{
    /**
     * Average carbon sequestration factor for Indonesian seagrass (tC/Ha/year).
     */
    public const DEFAULT_CARBON_FACTOR = 0.057;

    /**
     * CO₂ conversion factor: 1 tonne of Carbon = 3.67 tonnes of CO₂.
     */
    public const CO2_CONVERSION = 3.67;

    /**
     * Social cost of carbon in IDR per tonne CO₂ (approximate).
     */
    public const CARBON_PRICE_IDR = 75_000;

    /**
     * Average CO₂ emissions per car per year (tonnes).
     */
    private const CO2_PER_CAR_PER_YEAR = 4.6;

    /**
     * Average CO₂ absorption per tree per year (kg).
     */
    private const CO2_PER_TREE_PER_YEAR_KG = 22.0;

    /**
     * CO₂ per GWh of coal-fired electricity (tonnes).
     */
    private const CO2_PER_GWH = 900.0;

    /**
     * Calculate carbon stock for a single survey point.
     *
     * Formula: area_hectares × (coverage% / 100) × carbon_factor × CO₂_conversion
     *
     * @param Survey $survey       The survey containing coverage data
     * @param float  $areaHectares The area in hectares this survey point represents
     * @return float Carbon stock in tonnes CO₂
     */
    public function calculateForSurvey(Survey $survey, float $areaHectares): float
    {
        $coverageFraction = ($survey->total_coverage_percent ?? 0) / 100;

        return $areaHectares * $coverageFraction * self::DEFAULT_CARBON_FACTOR * self::CO2_CONVERSION;
    }

    /**
     * Calculate total carbon stock for a region.
     *
     * @return array{
     *     total_area_ha: float,
     *     avg_coverage: float,
     *     carbon_stock_tc: float,
     *     carbon_stock_tco2: float,
     *     equivalent_cars: int,
     *     equivalent_trees: int,
     *     equivalent_energy_gwh: float,
     *     economic_value_idr: float
     * }
     */
    public function calculateForRegion(int $regionId): array
    {
        $region = Region::findOrFail($regionId);
        $surveys = Survey::query()
            ->where('region_id', $regionId)
            ->published()
            ->get();

        $totalAreaHa = $region->area_hectares ?? 0;
        $avgCoverage = $surveys->avg('total_coverage_percent') ?? 0;
        $coverageFraction = $avgCoverage / 100;

        $carbonStockTc = $totalAreaHa * $coverageFraction * self::DEFAULT_CARBON_FACTOR;
        $carbonStockTco2 = $carbonStockTc * self::CO2_CONVERSION;

        return [
            'total_area_ha' => round($totalAreaHa, 2),
            'avg_coverage' => round($avgCoverage, 2),
            'carbon_stock_tc' => round($carbonStockTc, 4),
            'carbon_stock_tco2' => round($carbonStockTco2, 4),
            'equivalent_cars' => (int) round($carbonStockTco2 / self::CO2_PER_CAR_PER_YEAR),
            'equivalent_trees' => (int) round(($carbonStockTco2 * 1000) / self::CO2_PER_TREE_PER_YEAR_KG),
            'equivalent_energy_gwh' => round($carbonStockTco2 / self::CO2_PER_GWH, 4),
            'economic_value_idr' => round($carbonStockTco2 * self::CARBON_PRICE_IDR, 0),
        ];
    }

    /**
     * National-level carbon summary across all regions.
     *
     * @return array{
     *     total_regions: int,
     *     total_area_ha: float,
     *     total_surveys: int,
     *     avg_coverage: float,
     *     carbon_stock_tc: float,
     *     carbon_stock_tco2: float,
     *     equivalent_cars: int,
     *     equivalent_trees: int,
     *     equivalent_energy_gwh: float,
     *     economic_value_idr: float
     * }
     */
    public function getNationalSummary(): array
    {
        $data = DB::table('surveys')
            ->join('regions', 'surveys.region_id', '=', 'regions.id')
            ->where('surveys.status', 'published')
            ->select([
                DB::raw('COUNT(DISTINCT regions.id) as total_regions'),
                DB::raw('SUM(DISTINCT regions.area_hectares) as total_area_ha'),
                DB::raw('COUNT(surveys.id) as total_surveys'),
                DB::raw('AVG(surveys.total_coverage_percent) as avg_coverage'),
            ])
            ->first();

        $totalAreaHa = (float) ($data->total_area_ha ?? 0);
        $avgCoverage = (float) ($data->avg_coverage ?? 0);
        $coverageFraction = $avgCoverage / 100;

        $carbonStockTc = $totalAreaHa * $coverageFraction * self::DEFAULT_CARBON_FACTOR;
        $carbonStockTco2 = $carbonStockTc * self::CO2_CONVERSION;

        return [
            'total_regions' => (int) ($data->total_regions ?? 0),
            'total_area_ha' => round($totalAreaHa, 2),
            'total_surveys' => (int) ($data->total_surveys ?? 0),
            'avg_coverage' => round($avgCoverage, 2),
            'carbon_stock_tc' => round($carbonStockTc, 4),
            'carbon_stock_tco2' => round($carbonStockTco2, 4),
            'equivalent_cars' => (int) round($carbonStockTco2 / self::CO2_PER_CAR_PER_YEAR),
            'equivalent_trees' => (int) round(($carbonStockTco2 * 1000) / self::CO2_PER_TREE_PER_YEAR_KG),
            'equivalent_energy_gwh' => round($carbonStockTco2 / self::CO2_PER_GWH, 4),
            'economic_value_idr' => round($carbonStockTco2 * self::CARBON_PRICE_IDR, 0),
        ];
    }

    /**
     * Get yearly trend of carbon accumulation.
     *
     * @param int|null $regionId  Optional region filter
     * @param int      $fromYear  Start year (inclusive)
     * @param int      $toYear    End year (inclusive)
     * @return array<int, array{
     *     year: int,
     *     survey_count: int,
     *     avg_coverage: float,
     *     carbon_stock_tc: float,
     *     carbon_stock_tco2: float
     * }>
     */
    public function getCarbonTrend(?int $regionId = null, int $fromYear = 2019, int $toYear = 2025): array
    {
        $trend = [];

        for ($year = $fromYear; $year <= $toYear; $year++) {
            $query = Survey::query()
                ->published()
                ->whereYear('survey_date', $year);

            if ($regionId) {
                $query->where('region_id', $regionId);
            }

            $surveys = $query->get();
            $count = $surveys->count();
            $avgCoverage = $count > 0 ? $surveys->avg('total_coverage_percent') : 0;

            // Use regional area if single region, otherwise aggregate
            $totalAreaHa = 0;
            if ($regionId) {
                $totalAreaHa = Region::find($regionId)?->area_hectares ?? 0;
            } else {
                $totalAreaHa = (float) Region::query()
                    ->whereHas('surveys', function ($q) use ($year) {
                        $q->published()->whereYear('survey_date', $year);
                    })
                    ->sum('area_hectares');
            }

            $coverageFraction = $avgCoverage / 100;
            $carbonStockTc = $totalAreaHa * $coverageFraction * self::DEFAULT_CARBON_FACTOR;
            $carbonStockTco2 = $carbonStockTc * self::CO2_CONVERSION;

            $trend[] = [
                'year' => $year,
                'survey_count' => $count,
                'avg_coverage' => round($avgCoverage, 2),
                'carbon_stock_tc' => round($carbonStockTc, 4),
                'carbon_stock_tco2' => round($carbonStockTco2, 4),
            ];
        }

        return $trend;
    }
}
