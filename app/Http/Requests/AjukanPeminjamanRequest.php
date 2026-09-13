<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi pengajuan pinjam dari katalog anggota.
 */
class AjukanPeminjamanRequest extends FormRequest
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
            'jumlah' => ['required', 'integer', 'min:1'],
        ];
    }
}
