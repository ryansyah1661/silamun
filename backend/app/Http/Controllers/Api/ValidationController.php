<?php

namespace App\Http\Controllers\Api;

use App\Enums\SurveyStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Controller for survey validation workflow.
 *
 * Handles the review queue, approval/rejection of surveys,
 * and validation history logging. Restricted to verifikator
 * and super_admin roles.
 */
class ValidationController extends Controller
{
    /**
     * List all pending surveys awaiting validation.
     *
     * Paginated, sorted by submission date (oldest first).
     */
    public function queue(Request $request): JsonResponse
    {
        try {
            $query = Survey::where('status', SurveyStatus::PENDING)
                ->with(['surveyor', 'region'])
                ->withCount(['species', 'photos']);

            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('location_name', 'ILIKE', "%{$search}%")
                        ->orWhere('code', 'ILIKE', "%{$search}%");
                });
            }

            $perPage = min((int) $request->get('per_page', 20), 100);
            $surveys = $query->orderBy('created_at', 'asc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => SurveyResource::collection($surveys),
                'meta' => [
                    'current_page' => $surveys->currentPage(),
                    'last_page' => $surveys->lastPage(),
                    'per_page' => $surveys->perPage(),
                    'total' => $surveys->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat antrian validasi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Review (approve or reject) a survey.
     *
     * Creates a SurveyValidation log entry.
     * If approved: status → published, sets verified_by and verified_at.
     * If rejected: status → rejected, stores rejection reason.
     */
    public function review(ValidateSurveyRequest $request, Survey $survey): JsonResponse
    {
        if ($survey->status !== SurveyStatus::PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya survei berstatus pending yang dapat divalidasi.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $survey) {
                $user = $request->user();
                $action = $request->action;

                // Create validation log
                SurveyValidation::create([
                    'survey_id' => $survey->id,
                    'validator_id' => $user->id,
                    'action' => $action,
                    'comments' => $request->comments,
                ]);

                if ($action === 'approved') {
                    $survey->update([
                        'status' => SurveyStatus::PUBLISHED,
                        'verified_by' => $user->id,
                        'verified_at' => now(),
                    ]);

                    $message = 'Survei berhasil disetujui dan dipublikasikan.';
                } else {
                    $survey->update([
                        'status' => SurveyStatus::REJECTED,
                    ]);

                    $message = 'Survei ditolak.';
                }

                // TODO: Send notification to surveyor
                // Notification::send($survey->surveyor, new SurveyReviewedNotification($survey, $action));

                $survey->load(['surveyor', 'region', 'validations.validator']);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => new SurveyResource($survey),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses validasi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Validation history log.
     *
     * Shows all past validation actions with filtering.
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $query = SurveyValidation::with(['survey.surveyor', 'survey.region', 'validator'])
                ->orderByDesc('created_at');

            if ($request->filled('action')) {
                $query->where('action', $request->action);
            }

            if ($request->filled('validator_id')) {
                $query->where('validator_id', $request->validator_id);
            }

            $perPage = min((int) $request->get('per_page', 20), 100);
            $history = $query->paginate($perPage);

            $data = $history->getCollection()->map(fn ($v) => [
                'id' => $v->id,
                'action' => $v->action,
                'comments' => $v->comments,
                'validator' => [
                    'id' => $v->validator?->id,
                    'name' => $v->validator?->name,
                ],
                'survey' => [
                    'id' => $v->survey?->id,
                    'code' => $v->survey?->code,
                    'location_name' => $v->survey?->location_name,
                    'surveyor' => $v->survey?->surveyor?->name,
                ],
                'created_at' => $v->created_at?->toIso8601String(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $data,
                'meta' => [
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                    'per_page' => $history->perPage(),
                    'total' => $history->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat riwayat validasi.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
