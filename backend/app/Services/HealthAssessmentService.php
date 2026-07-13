<?php

namespace App\Services;

use App\Enums\HealthStatus;
use App\Models\Survey;
use Illuminate\Support\Collection;

class HealthAssessmentService
{
    /**
     * Determine health status based on Kepmen LH No. 200/2004 criteria:
     * - Sehat / Kaya    : >= 60%
     * - Kurang Sehat    : 30% – 59.9%
     * - Miskin          : <= 29.9%
     *
     * @param float $coveragePercent Seagrass coverage percentage (0–100)
     */
    public function assess(float $coveragePercent): HealthStatus
    {
        return HealthStatus::fromCoverage($coveragePercent);
    }

    /**
     * Get summary statistics for a collection of surveys.
     *
     * @param Collection<int, Survey> $surveys
     * @return array{
     *     total: int,
     *     sehat: int,
     *     kurang_sehat: int,
     *     miskin: int,
     *     percentages: array{sehat: float, kurang_sehat: float, miskin: float},
     *     avg_coverage: float
     * }
     */
    public function getSummaryStatistics(Collection $surveys): array
    {
        $total = $surveys->count();

        $counts = [
            'sehat' => 0,
            'kurang_sehat' => 0,
            'miskin' => 0,
        ];

        foreach ($surveys as $survey) {
            $status = $survey->health_status ?? HealthStatus::fromCoverage(
                $survey->total_coverage_percent ?? 0
            );

            match ($status) {
                HealthStatus::SEHAT => $counts['sehat']++,
                HealthStatus::KURANG_SEHAT => $counts['kurang_sehat']++,
                HealthStatus::MISKIN => $counts['miskin']++,
            };
        }

        $percentages = $total > 0 ? [
            'sehat' => round(($counts['sehat'] / $total) * 100, 1),
            'kurang_sehat' => round(($counts['kurang_sehat'] / $total) * 100, 1),
            'miskin' => round(($counts['miskin'] / $total) * 100, 1),
        ] : [
            'sehat' => 0.0,
            'kurang_sehat' => 0.0,
            'miskin' => 0.0,
        ];

        $avgCoverage = $total > 0
            ? round($surveys->avg('total_coverage_percent'), 2)
            : 0.0;

        return [
            'total' => $total,
            'sehat' => $counts['sehat'],
            'kurang_sehat' => $counts['kurang_sehat'],
            'miskin' => $counts['miskin'],
            'percentages' => $percentages,
            'avg_coverage' => $avgCoverage,
        ];
    }

    /**
     * Calculate trend data by comparing current vs previous period.
     *
     * @return array{
     *     current_year: int,
     *     previous_year: int,
     *     current: array,
     *     previous: array,
     *     trend: array{coverage_change: float, total_change: int}
     * }
     */
    public function calculateTrend(?string $regionId, int $currentYear, int $previousYear): array
    {
        $currentSurveys = Survey::query()
            ->whereYear('survey_date', $currentYear)
            ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
            ->get();

        $previousSurveys = Survey::query()
            ->whereYear('survey_date', $previousYear)
            ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
            ->get();

        $currentStats = $this->getSummaryStatistics($currentSurveys);
        $previousStats = $this->getSummaryStatistics($previousSurveys);

        $coverageChange = $currentStats['avg_coverage'] - $previousStats['avg_coverage'];
        $totalChange = $currentStats['total'] - $previousStats['total'];

        return [
            'current_year' => $currentYear,
            'previous_year' => $previousYear,
            'current' => $currentStats,
            'previous' => $previousStats,
            'trend' => [
                'coverage_change' => round($coverageChange, 2),
                'total_change' => $totalChange,
            ],
        ];
    }
}
