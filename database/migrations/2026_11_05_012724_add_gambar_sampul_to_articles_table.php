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
    Schema::table('articles', function (Blueprint $table) {
        // Menambahkan kolom gambar_sampul yang sifatnya opsional (nullable)
        $table->string('gambar_sampul')->nullable()->after('konten');
    });
}

public function down(): void
{
    Schema::table('articles', function (Blueprint $table) {
        $table->dropColumn('gambar_sampul');
    });
}
};
