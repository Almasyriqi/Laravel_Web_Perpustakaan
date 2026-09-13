<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Parameter pencarian & filter katalog buku anggota (query string GET).
 */
class KatalogRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'integer', 'exists:kategori,id'],
            'tersedia' => ['nullable', 'boolean'],
        ];
    }
}
