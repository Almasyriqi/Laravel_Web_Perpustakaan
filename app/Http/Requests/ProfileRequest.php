<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi ubah profil sendiri (semua role).
 */
class ProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $userId = $this->user()->id;
        $anggota = $this->user()->isAnggota();

        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($userId)],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'tgl_lahir' => [$anggota ? 'required' : 'nullable', 'date'],
            'nim' => [
                $anggota ? 'required' : 'nullable',
                'numeric',
                'digits_between:1,19',
                Rule::unique('anggota', 'nim')->ignore($userId, 'user_id'),
            ],
            'jurusan' => [$anggota ? 'required' : 'nullable', 'string', 'max:30'],
        ];
    }
}
