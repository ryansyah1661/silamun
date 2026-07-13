<?php

namespace App\Http\Controllers\Api;

use App\Enums\SurveyStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Services\HealthAssessmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Controller for managing seagrass survey operations.
 *
 * Handles CRUD operations, survey submission workflow,
 * and filtering/searching capabilities.
 */
class SurveyController extends Controller
{
    public function __construct(
        private readonly HealthAssessmentService $healthService,
    ) {}

    /**
     * List surveys with filtering, searching, and pagination.
     *
     * Filters: status, region_id, health_status, date_from, date_to, search
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Survey::query()
                ->with(['surveyor', 'region'])
                ->withCount(['species', 'photos']);

            // Scope non-admin users to their own surveys
            if ($request->user()->role === UserRole::SURVEYOR) {
                $query->where('surveyor_id', $request->user()->id);
            }

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            if ($request->filled('health_status')) {
                $query->where('health_status', $request->health_status);
            }

            if ($request->filled('date_from')) {
                $query->where('survey_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('survey_date', '<=', $request->date_to);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('location_name', 'ILIKE', "%{$search}%")
                        ->orWhere('code', 'ILIKE', "%{$search}%")
                        ->orWhere('notes', 'ILIKE', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDir = $request->get('sort_dir', 'desc');
            $allowedSorts = ['created_at', 'survey_date', 'total_coverage_percent', 'location_name'];

            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
            }

            $perPage = min((int) $request->get('per_page', 20), 100);
            $surveys = $query->paginate($perPage);

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
                'message' => 'Gagal memuat data survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new survey with species and photos.
     */
    public function store(StoreSurveyRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Generate unique survey code
                $code = 'SRV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

                // Calculate health status from coverage
                $healthStatus = $this->healthService->assessFromCoverage(
                    $request->total_coverage_percent
                );

                // Create the survey
                $survey = Survey::create([
                    'code' => $code,
                    'surveyor_id' => $request->user()->id,
                    'location_name' => $request->location_name,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'survey_date' => $request->survey_date,
                    'region_id' => $request->region_id,
                    'sampling_method' => $request->sampling_method,
                    'water_temperature' => $request->water_temperature,
                    'salinity' => $request->salinity,
                    'water_depth' => $request->water_depth,
                    'substrate_type' => $request->substrate_type,
                    'total_coverage_percent' => $request->total_coverage_percent,
                    'health_status' => $healthStatus,
                    'notes' => $request->notes,
                    'status' => SurveyStatus::DRAFT,
                ]);

                // Attach species data
                if ($request->has('species') && is_array($request->species)) {
                    $speciesData = [];
                    foreach ($request->species as $speciesEntry) {
                        $speciesData[$speciesEntry['species_id']] = [
                            'coverage_percent' => $speciesEntry['coverage_percent'],
                            'density' => $speciesEntry['density'] ?? null,
                            'frequency' => $speciesEntry['frequency'] ?? null,
                        ];
                    }
                    $survey->species()->attach($speciesData);

                    // Set dominant species
                    $dominantSpecies = collect($request->species)
                        ->sortByDesc('coverage_percent')
                        ->first();
                    if ($dominantSpecies) {
                        $dominant = \App\Models\Species::find($dominantSpecies['species_id']);
                        if ($dominant) {
                            $survey->update(['dominant_species' => $dominant->name]);
                        }
                    }
                }

                // Upload photos
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $path = $photo->store("surveys/{$survey->id}", 'public');
                        $survey->photos()->create([
                            'url' => Storage::disk('public')->url($path),
                            'thumbnail_url' => Storage::disk('public')->url($path),
                            'path' => $path,
                            'type' => 'survey',
                            'caption' => null,
                        ]);
                    }
                }

                $survey->load(['surveyor', 'region', 'species', 'photos']);
                $survey->loadCount(['species', 'photos']);

                return response()->json([
                    'success' => true,
                    'message' => 'Survei berhasil dibuat.',
                    'data' => new SurveyResource($survey),
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a single survey with all related data.
     */
    public function show(Survey $survey): JsonResponse
    {
        try {
            $survey->load(['surveyor', 'region', 'species', 'photos', 'validations.validator', 'verifiedBy']);
            $survey->loadCount(['species', 'photos']);

            return response()->json([
                'success' => true,
                'data' => new SurveyResource($survey),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update an existing survey (only if draft or rejected).
     */
    public function update(UpdateSurveyRequest $request, Survey $survey): JsonResponse
    {
        // Only allow updates on draft or rejected surveys
        if (! in_array($survey->status, [SurveyStatus::DRAFT, SurveyStatus::REJECTED])) {
            return response()->json([
                'success' => false,
                'message' => 'Survei hanya dapat diubah jika berstatus draft atau ditolak.',
            ], 422);
        }

        try {
            return DB::transaction(function () use ($request, $survey) {
                $data = $request->validated();

                // Recalculate health status if coverage changed
                if (isset($data['total_coverage_percent'])) {
                    $data['health_status'] = $this->healthService->assessFromCoverage(
                        $data['total_coverage_percent']
                    );
                }

                // Remove nested data before mass-update
                $speciesData = $data['species'] ?? null;
                $photosData = $data['photos'] ?? null;
                unset($data['species'], $data['photos']);

                $survey->update($data);

                // Sync species if provided
                if ($speciesData !== null) {
                    $syncData = [];
                    foreach ($speciesData as $entry) {
                        $syncData[$entry['species_id']] = [
                            'coverage_percent' => $entry['coverage_percent'],
                            'density' => $entry['density'] ?? null,
                            'frequency' => $entry['frequency'] ?? null,
                        ];
                    }
                    $survey->species()->sync($syncData);

                    // Update dominant species
                    $dominantEntry = collect($speciesData)->sortByDesc('coverage_percent')->first();
                    if ($dominantEntry) {
                        $dominant = \App\Models\Species::find($dominantEntry['species_id']);
                        $survey->update(['dominant_species' => $dominant?->name]);
                    }
                }

                // Upload additional photos if provided
                if ($request->hasFile('photos')) {
                    foreach ($request->file('photos') as $photo) {
                        $path = $photo->store("surveys/{$survey->id}", 'public');
                        $survey->photos()->create([
                            'url' => Storage::disk('public')->url($path),
                            'thumbnail_url' => Storage::disk('public')->url($path),
                            'path' => $path,
                            'type' => 'survey',
                            'caption' => null,
                        ]);
                    }
                }

                $survey->load(['surveyor', 'region', 'species', 'photos']);
                $survey->loadCount(['species', 'photos']);

                return response()->json([
                    'success' => true,
                    'message' => 'Survei berhasil diperbarui.',
                    'data' => new SurveyResource($survey),
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Soft-delete a survey (only if draft or admin).
     */
    public function destroy(Request $request, Survey $survey): JsonResponse
    {
        $user = $request->user();

        // Only draft surveys can be deleted by owner, admins can delete any
        if ($user->role !== UserRole::SUPER_ADMIN && $survey->status !== SurveyStatus::DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya survei berstatus draft yang dapat dihapus.',
            ], 422);
        }

        // Check ownership for non-admins
        if ($user->role !== UserRole::SUPER_ADMIN && $user->id !== $survey->surveyor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menghapus survei ini.',
            ], 403);
        }

        try {
            $survey->delete();

            return response()->json([
                'success' => true,
                'message' => 'Survei berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Submit a draft survey for validation (change status from draft to pending).
     */
    public function submit(Request $request, Survey $survey): JsonResponse
    {
        $user = $request->user();

        // Check ownership
        if ($user->role !== UserRole::SUPER_ADMIN && $user->id !== $survey->surveyor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk mengirim survei ini.',
            ], 403);
        }

        if (! in_array($survey->status, [SurveyStatus::DRAFT, SurveyStatus::REJECTED])) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya survei berstatus draft atau ditolak yang dapat dikirim.',
            ], 422);
        }

        try {
            $survey->update(['status' => SurveyStatus::PENDING]);

            return response()->json([
                'success' => true,
                'message' => 'Survei berhasil dikirim untuk validasi.',
                'data' => new SurveyResource($survey->fresh()->load(['surveyor', 'region'])),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim survei.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
