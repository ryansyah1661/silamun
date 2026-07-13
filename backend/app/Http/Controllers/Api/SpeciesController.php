<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\SpeciesResource;
use App\Models\Species;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Controller for managing seagrass species data.
 *
 * Provides public listing/search and admin-only CRUD operations
 * for the species reference table.
 */
class SpeciesController extends Controller
{
    /**
     * List all active species with search and family filter.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Species::query()->withCount('surveys');

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'ILIKE', "%{$search}%")
                        ->orWhere('local_name', 'ILIKE', "%{$search}%")
                        ->orWhere('family', 'ILIKE', "%{$search}%");
                });
            }

            if ($request->filled('family')) {
                $query->where('family', $request->family);
            }

            if ($request->filled('iucn_status')) {
                $query->where('iucn_status', $request->iucn_status);
            }

            $species = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => SpeciesResource::collection($species),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data spesies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Show a single species detail with survey count and distribution.
     */
    public function show(Species $species): JsonResponse
    {
        try {
            $species->loadCount('surveys');

            // Get distribution data (regions where this species has been found)
            $distribution = $species->surveys()
                ->select('region_id')
                ->with('region:id,name,code')
                ->whereNotNull('region_id')
                ->get()
                ->pluck('region')
                ->unique('id')
                ->values()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'name' => $r->name,
                    'code' => $r->code,
                ]);

            return response()->json([
                'success' => true,
                'data' => new SpeciesResource($species),
                'distribution' => $distribution,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail spesies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Create a new species (admin only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:species,name'],
            'local_name' => ['nullable', 'string', 'max:255'],
            'family' => ['required', 'string', 'max:255'],
            'order' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'morphology' => ['nullable', 'string'],
            'habitat' => ['nullable', 'string'],
            'depth_range' => ['nullable', 'string', 'max:50'],
            'iucn_status' => ['nullable', 'string', 'in:LC,NT,VU,EN,CR,EW,EX,DD,NE'],
            'carbon_factor' => ['nullable', 'numeric', 'min:0'],
            'photo_url' => ['nullable', 'url', 'max:500'],
        ]);

        try {
            $validated['slug'] = Str::slug($validated['name']);

            $species = Species::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Spesies berhasil ditambahkan.',
                'data' => new SpeciesResource($species),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan spesies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Update an existing species (admin only).
     */
    public function update(Request $request, Species $species): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255', 'unique:species,name,' . $species->id],
            'local_name' => ['nullable', 'string', 'max:255'],
            'family' => ['sometimes', 'string', 'max:255'],
            'order' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'morphology' => ['nullable', 'string'],
            'habitat' => ['nullable', 'string'],
            'depth_range' => ['nullable', 'string', 'max:50'],
            'iucn_status' => ['nullable', 'string', 'in:LC,NT,VU,EN,CR,EW,EX,DD,NE'],
            'carbon_factor' => ['nullable', 'numeric', 'min:0'],
            'photo_url' => ['nullable', 'url', 'max:500'],
        ]);

        try {
            if (isset($validated['name'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $species->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Spesies berhasil diperbarui.',
                'data' => new SpeciesResource($species->fresh()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui spesies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Soft-delete a species (admin only).
     */
    public function destroy(Species $species): JsonResponse
    {
        try {
            $species->delete();

            return response()->json([
                'success' => true,
                'message' => 'Spesies berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus spesies.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
