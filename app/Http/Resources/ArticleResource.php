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
        'kategori' => $this->category ? $this->category->nama_kategori : 'Tanpa Kategori',
        'judul_artikel' => $this->judul,
        'isi_konten' => $this->konten,
        // Menampilkan array nama tag (contoh: ["laravel", "api"])
        'tags' => $this->tags->pluck('nama_tag'),
        'dibuat_pada' => $this->created_at->format('d F Y'),
    ];
}
}
