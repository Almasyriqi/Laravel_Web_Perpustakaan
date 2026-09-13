<?php

namespace App\Http\Requests;

use App\Models\Anggota;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi tambah (POST) dan ubah (PUT) anggota oleh admin/petugas.
 */
class AnggotaRequest extends FormRequest
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
        $anggota = $this->isMethod('POST') ? null : Anggota::findOrFail($this->route('anggotum'));
        $userId = $anggota?->user_id;

        return [
            'username' => ['required', 'string', 'max:20', Rule::unique('users', 'username')->ignore($userId)],
            'password' => $this->isMethod('POST') ? ['required', 'string', 'min:8'] : ['nullable'],
            'nim' => ['required', 'numeric', 'digits_between:1,19', Rule::unique('anggota', 'nim')->ignore($anggota?->nim, 'nim')],
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'jurusan' => ['required', 'string', 'max:30'],
            'tgl_lahir' => ['required', 'date'],
            'no_hp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
        ];
    }
}
