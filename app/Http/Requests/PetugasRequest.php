<?php

namespace App\Http\Requests;

use App\Models\Petugas;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah (POST) dan ubah (PUT) akun petugas.
 */
class PetugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->isMethod('POST') ? null : Petugas::findOrFail($this->route('petuga'))->user_id;

        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($userId)],
            'password' => $this->isMethod('POST') ? ['required', 'string', 'min:8'] : ['nullable'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'tgl_lahir' => ['required', 'date'],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ];
    }
}
