<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
public function toArray(Request $request): array
{
    return [
        'id_artikel' => $this->id,
        // Mengecek apakah artikel punya kategori, jika ada tampilkan namanya, jika tidak 'Tanpa Kategori'
        'kategori' => $this->category ? $this->category->nama_kategori : 'Tanpa Kategori',
        'judul_artikel' => $this->judul,
        'isi_konten' => $this->konten,
        'dibuat_pada' => $this->created_at->format('d F Y'),
    ];
}
}
