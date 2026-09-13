<?php

namespace App\Http\Requests;

use App\Services\PeminjamanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi CRUD peminjaman oleh admin (POST tambah, PUT edit bebas).
 * Kecukupan stok tidak dicek di sini melainkan di PeminjamanService di dalam lock.
 */
class PeminjamanRequest extends FormRequest
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
        $rules = [
            'jumlah' => ['required', 'integer', 'min:1'],
            'tgl_pinjam' => ['required', 'date'],
            'status' => ['required', Rule::in(PeminjamanService::SEMUA_STATUS)],
        ];

        if ($this->isMethod('POST')) {
            return $rules + [
                'anggota' => ['required', 'exists:anggota,nim'],
                'judul' => ['required', 'exists:buku,id'],
            ];
        }

        return $rules + [
            'tgl_kembali' => ['nullable', 'date', 'after_or_equal:tgl_pinjam'],
            'perpanjang' => ['nullable', 'boolean'],
        ];
    }
}
