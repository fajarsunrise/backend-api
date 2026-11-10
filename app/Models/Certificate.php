<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'title',
        'issued_by',
        'issue_date',
        'image_url',
    ];

    /**
     * Casting kolom tanggal agar otomatis menjadi format objek Date
     */
    protected $casts = [
        'issue_date' => 'date',
    ];
}

