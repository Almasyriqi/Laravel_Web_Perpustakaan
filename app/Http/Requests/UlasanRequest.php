<?php

namespace App\Http\Requests;

use App\Models\Ulasan;
use Illuminate\Foundation\Http\FormRequest;

class UlasanRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'min:'.Ulasan::RATING_MIN, 'max:'.Ulasan::RATING_MAKS],
            'komentar' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
