<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Artikel ini milik (belongsTo) sebuah Kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
