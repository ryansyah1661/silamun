<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\SurveyStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin dashboard controller for SI-LAMUN.
 *
 * Provides administrative overview statistics,
 * pending survey counts, monthly charts, and user statistics.
 * Restricted to super_admin role.
 */
class AdminDashboardController extends Controller
{
    /**
     * Admin dashboard overview.
     *
     * Returns: total surveys, pending count, approved/rejected this month,
     * surveys by month (chart data), and recent pending items.
     */
    public function overview(): JsonResponse
    {
        try {
            $totalSurveys = Survey::count();
            $pendingCount = Survey::where('status', SurveyStatus::PENDING)->count();

            $startOfMonth = now()->startOfMonth();
            $approvedThisMonth = Survey::where('status', SurveyStatus::PUBLISHED)
                ->where('verified_at', '>=', $startOfMonth)
                ->count();
            $rejectedThisMonth = Survey::where('status', SurveyStatus::REJECTED)
                ->where('updated_at', '>=', $startOfMonth)
                ->count();

            // Surveys by month (last 12 months)
            $surveysByMonth = Survey::select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as total'),
                    DB::raw("COUNT(*) FILTER (WHERE status = 'published') as published"),
                    DB::raw("COUNT(*) FILTER (WHERE status = 'pending') as pending"),
                    DB::raw("COUNT(*) FILTER (WHERE status = 'rejected') as rejected")
                )
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy(DB::raw("TO_CHAR(created_at, 'YYYY-MM')"))
                ->orderBy('month')
                ->get();

            // Recent pending items
            $recentPending = Survey::where('status', SurveyStatus::PENDING)
                ->with(['surveyor:id,name', 'region:id,name'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get()
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'code' => $s->code,
                    'location_name' => $s->location_name,
                    'surveyor' => $s->surveyor?->name,
                    'region' => $s->region?->name,
                    'created_at' => $s->created_at?->toIso8601String(),
                ]);

            // Survey status breakdown
            $statusBreakdown = Survey::select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(fn ($item) => [
                    'status' => $item->status?->value,
                    'label' => $item->status?->label(),
                    'count' => $item->count,
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'total_surveys' => $totalSurveys,
                    'pending_count' => $pendingCount,
                    'approved_this_month' => $approvedThisMonth,
                    'rejected_this_month' => $rejectedThisMonth,
                    'status_breakdown' => $statusBreakdown,
                    'surveys_by_month' => $surveysByMonth,
                    'recent_pending' => $recentPending,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat overview admin.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * User statistics.
     *
     * Returns: user count by role, most active surveyors (by survey count).
     */
    public function userStats(): JsonResponse
    {
        try {
            // User count by role
            $usersByRole = User::select('role', DB::raw('COUNT(*) as count'))
                ->groupBy('role')
                ->get()
                ->map(fn ($item) => [
                    'role' => $item->role?->value,
                    'label' => $item->role?->label(),
                    'count' => $item->count,
                ]);

            $totalUsers = User::count();
            $activeUsers = User::where('is_active', true)->count();

            // Most active surveyors (top 10)
            $activeSurveyors = User::where('role', UserRole::SURVEYOR)
                ->withCount('surveys')
                ->orderByDesc('surveys_count')
                ->limit(10)
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'institution' => $u->institution,
                    'surveys_count' => $u->surveys_count,
                    'is_active' => $u->is_active,
                ]);

            // New users this month
            $newUsersThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'new_users_this_month' => $newUsersThisMonth,
                    'users_by_role' => $usersByRole,
                    'most_active_surveyors' => $activeSurveyors,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat statistik pengguna.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
