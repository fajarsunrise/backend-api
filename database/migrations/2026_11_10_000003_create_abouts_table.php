<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('abouts', function (Blueprint $table) {
            $table->id();
            $table->string('hero_greeting')->nullable();
            $table->text('hero_description')->nullable();
            $table->string('marquee_text')->nullable();
            $table->text('about_bio')->nullable();
            $table->string('years_experience')->nullable();
            $table->string('projects_completed')->nullable();
            $table->string('profile_image_url')->nullable();
            $table->string('contact_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abouts');
    }
};

