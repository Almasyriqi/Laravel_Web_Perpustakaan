<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi tambah (POST) dan ubah (PUT) buku; sampul wajib hanya saat tambah.
 */
class BukuRequest extends FormRequest
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
            'kategori' => ['required', 'exists:kategori,id'],
            'judul' => ['required', 'string', 'max:255'],
            'penerbit' => ['required', 'string', 'max:255'],
            'penulis' => ['required', 'string', 'max:255'],
            'keterangan' => ['required', 'string'],
            'stok' => ['required', 'integer', 'min:0'],
            'gambar' => [$this->isMethod('POST') ? 'required' : 'nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ];
    }
}
