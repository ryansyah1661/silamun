<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for creating a new survey.
 *
 * Validates all survey fields including location coordinates,
 * environmental parameters, species data, and photo uploads.
 */
class StoreSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * User must be authenticated and have surveyor or admin role.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && in_array($user->role, [
            UserRole::SURVEYOR,
            UserRole::VERIFIKATOR,
            UserRole::SUPER_ADMIN,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Location
            'location_name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'survey_date' => ['required', 'date', 'before_or_equal:today'],
            'region_id' => ['nullable', 'exists:regions,id'],

            // Sampling method
            'sampling_method' => ['required', 'in:transek_kuadrat,transek_garis,plot_sampling'],

            // Environmental parameters
            'water_temperature' => ['nullable', 'numeric', 'between:0,45'],
            'salinity' => ['nullable', 'numeric', 'between:0,50'],
            'water_depth' => ['nullable', 'numeric', 'between:0,50'],
            'substrate_type' => ['nullable', 'in:pasir,lumpur,pasir_berlumpur,karang,campuran'],

            // Coverage
            'total_coverage_percent' => ['required', 'numeric', 'between:0,100'],

            // Notes
            'notes' => ['nullable', 'string'],

            // Species data (array of species observations)
            'species' => ['nullable', 'array'],
            'species.*.species_id' => ['required_with:species', 'exists:species,id'],
            'species.*.coverage_percent' => ['required_with:species', 'numeric', 'between:0,100'],
            'species.*.density' => ['nullable', 'numeric', 'min:0'],
            'species.*.frequency' => ['nullable', 'numeric', 'between:0,100'],

            // Photo uploads
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'location_name.required' => 'Nama lokasi survei wajib diisi.',
            'latitude.required' => 'Koordinat lintang wajib diisi.',
            'latitude.between' => 'Koordinat lintang harus antara -90 dan 90.',
            'longitude.required' => 'Koordinat bujur wajib diisi.',
            'longitude.between' => 'Koordinat bujur harus antara -180 dan 180.',
            'survey_date.required' => 'Tanggal survei wajib diisi.',
            'survey_date.before_or_equal' => 'Tanggal survei tidak boleh melebihi hari ini.',
            'sampling_method.required' => 'Metode sampling wajib dipilih.',
            'sampling_method.in' => 'Metode sampling harus salah satu dari: transek kuadrat, transek garis, plot sampling.',
            'total_coverage_percent.required' => 'Persentase tutupan total wajib diisi.',
            'total_coverage_percent.between' => 'Persentase tutupan harus antara 0 dan 100.',
            'species.*.species_id.exists' => 'Spesies yang dipilih tidak ditemukan.',
            'species.*.coverage_percent.between' => 'Persentase tutupan spesies harus antara 0 dan 100.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'photos.*.max' => 'Ukuran gambar maksimal 5MB.',
            'photos.max' => 'Maksimal 10 foto per survei.',
        ];
    }
}
