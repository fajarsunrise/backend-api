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
        // Pastikan category_id berupa angka dan benar-benar ada di tabel categories
        'category_id' => 'nullable|integer|exists:categories,id'
    ];
}
}
