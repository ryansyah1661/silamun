<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for updating an existing survey.
 *
 * Similar to StoreSurveyRequest but all fields are optional.
 * Authorization checks that the user owns the survey or is an admin.
 */
class UpdateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * User must own the survey or be an admin.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $survey = $this->route('survey');

        if (! $user || ! $survey) {
            return false;
        }

        // Admin can update any survey
        if ($user->role === UserRole::SUPER_ADMIN) {
            return true;
        }

        // Surveyor can only update their own surveys
        return $user->id === $survey->surveyor_id;
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
            'location_name' => ['sometimes', 'string', 'max:255'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'survey_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'region_id' => ['nullable', 'exists:regions,id'],

            // Sampling method
            'sampling_method' => ['sometimes', 'in:transek_kuadrat,transek_garis,plot_sampling'],

            // Environmental parameters
            'water_temperature' => ['nullable', 'numeric', 'between:0,45'],
            'salinity' => ['nullable', 'numeric', 'between:0,50'],
            'water_depth' => ['nullable', 'numeric', 'between:0,50'],
            'substrate_type' => ['nullable', 'in:pasir,lumpur,pasir_berlumpur,karang,campuran'],

            // Coverage
            'total_coverage_percent' => ['sometimes', 'numeric', 'between:0,100'],

            // Notes
            'notes' => ['nullable', 'string'],

            // Species data
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
            'latitude.between' => 'Koordinat lintang harus antara -90 dan 90.',
            'longitude.between' => 'Koordinat bujur harus antara -180 dan 180.',
            'survey_date.before_or_equal' => 'Tanggal survei tidak boleh melebihi hari ini.',
            'sampling_method.in' => 'Metode sampling harus salah satu dari: transek kuadrat, transek garis, plot sampling.',
            'total_coverage_percent.between' => 'Persentase tutupan harus antara 0 dan 100.',
            'species.*.species_id.exists' => 'Spesies yang dipilih tidak ditemukan.',
            'photos.*.image' => 'File harus berupa gambar.',
            'photos.*.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'photos.*.max' => 'Ukuran gambar maksimal 5MB.',
        ];
    }
}
