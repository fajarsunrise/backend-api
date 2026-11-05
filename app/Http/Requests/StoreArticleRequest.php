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
        'tags' => 'nullable|array',
        'tags.*' => 'integer|exists:tags,id',
        // Aturan baru untuk file unggahan
        'gambar_sampul' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048' // Maksimal 2MB
    ];
}
}
