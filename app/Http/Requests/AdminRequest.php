<?php

namespace App\Http\Requests;

use App\Models\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah (POST) dan ubah (PUT) akun admin.
 */
class AdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi sudah ditangani middleware role
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->isMethod('POST') ? null : Admin::findOrFail($this->route('admin'))->user_id;

        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($userId)],
            'password' => $this->isMethod('POST') ? ['required', 'string', 'min:8'] : ['nullable'],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ];
    }
}
