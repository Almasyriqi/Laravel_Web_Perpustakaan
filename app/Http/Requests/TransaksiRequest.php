<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi peminjaman langsung di loket oleh petugas.
 */
class TransaksiRequest extends FormRequest
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
        return [
            'anggota' => ['required', 'exists:anggota,nim'],
            'judul' => ['required', 'exists:buku,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
        ];
    }
}
