<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Tag dimiliki oleh banyak (belongsToMany) Artikel
    public function articles()
    {
        return $this->belongsToMany(Article::class);
    }
}
