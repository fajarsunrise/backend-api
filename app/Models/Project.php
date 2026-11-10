<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'title',
        'description',
        'image_url',
        'technologies',
        'demo_link',
    ];

    /**
     * Konversi otomatis kolom technologies menjadi array saat dibaca dari database
     */
    protected $casts = [
        'technologies' => 'array',
    ];
}

