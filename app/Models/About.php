<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class About extends Model
{
    use HasFactory;

    protected $table = 'abouts';

    protected $fillable = [
        'bio',
        'years_experience',
        'projects_completed',
        'gpa',
        'profile_image_url',
        'contact_email',
    ];
}
