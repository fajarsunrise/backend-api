<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ubah menjadi true
        return true;
    }

public function rules(): array
{
    return [
        'judul' => 'required|string|max:255',
        'konten' => 'required|string',
        'category_id' => 'nullable|integer|exists:categories,id',
        // Validasi untuk Tag
        'tags' => 'nullable|array', // Harus berupa array (contoh: [1, 2, 3])
        'tags.*' => 'integer|exists:tags,id' // Setiap isi array harus berupa angka dan ada di tabel tags
    ];
}
}
