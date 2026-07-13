<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Form Request for survey validation (approve/reject).
 *
 * Only verifikator and super_admin roles can validate surveys.
 * Rejection requires comments explaining the reason.
 */
class ValidateSurveyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * User must be verifikator or super_admin.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && in_array($user->role, [
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
            'action' => ['required', 'in:approved,rejected'],
            'comments' => ['required_if:action,rejected', 'nullable', 'string', 'max:1000'],
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
            'action.required' => 'Aksi validasi wajib dipilih.',
            'action.in' => 'Aksi harus berupa approved atau rejected.',
            'comments.required_if' => 'Komentar wajib diisi jika survei ditolak.',
        ];
    }
}
