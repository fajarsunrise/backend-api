<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $table = 'abouts';

    protected $fillable = [
        'hero_greeting',
        'hero_description',
        'marquee_text',
        'about_bio',
        'years_experience',
        'projects_completed',
        'profile_image_url',
        'contact_email',
    ];
}

