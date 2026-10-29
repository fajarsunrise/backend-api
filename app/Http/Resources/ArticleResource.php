<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Mentransformasi data database menjadi format yang rapi untuk frontend
        return [
            'id_artikel' => $this->id,
            'judul_artikel' => $this->judul,
            'isi_konten' => $this->konten,
            // Mengubah format waktu bawaan menjadi lebih manusiawi
            'dibuat_pada' => $this->created_at->format('d F Y'),
        ];
    }
}
