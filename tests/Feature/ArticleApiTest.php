<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    // RefreshDatabase memastikan database testing selalu kosong setiap kali test dijalankan
    use RefreshDatabase;

    public function test_user_yang_sudah_login_bisa_melihat_daftar_artikel(): void
    {
        $this->withoutExceptionHandling();
        // 1. Persiapan Data (Arrange)
        // Buat 1 user palsu dan 3 artikel palsu di memori
        $user = User::factory()->create();
        Article::create(['judul' => 'Artikel 1', 'konten' => 'Isi konten 1']);
        Article::create(['judul' => 'Artikel 2', 'konten' => 'Isi konten 2']);
        Article::create(['judul' => 'Artikel 3', 'konten' => 'Isi konten 3']);

        // 2. Tindakan (Act)
        // Simulasikan user tersebut login menggunakan Sanctum, lalu akses endpoint GET /api/articles
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/articles');

        // 3. Verifikasi (Assert)
        // Pastikan API membalas dengan status 200 OK
        $response->assertStatus(200);

        // Pastikan data yang dikembalikan berjumlah 3 sesuai yang kita buat
        $response->assertJsonCount(3, 'data');
    }

    public function test_user_yang_belum_login_akan_ditolak(): void
    {
        // Tindakan: Langsung akses tanpa actingAs (tanpa token)
        $response = $this->getJson('/api/articles');

        // Verifikasi: Pastikan API menolak dengan status 401 (Unauthenticated)
        $response->assertStatus(401);
    }

    public function test_user_yang_sudah_login_bisa_membuat_artikel_baru(): void
    {
        // 1. Persiapan Data (Arrange)
        // Buat 1 user palsu
        $user = User::factory()->create();

        // Siapkan data artikel yang ingin dikirim (payload)
        $payload = [
            'judul' => 'Judul Artikel Testing',
            'konten' => 'Ini adalah isi dari artikel testing otomatis.'
        ];

        // 2. Tindakan (Act)
        // Login sebagai user tersebut, lalu kirim POST request beserta datanya
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/articles', $payload);

        // 3. Verifikasi (Assert)
        // Pastikan API membalas dengan status 201 (Created)
        $response->assertStatus(201);

        // Pastikan database benar-benar menyimpan artikel dengan judul tersebut
        $this->assertDatabaseHas('articles', [
            'judul' => 'Judul Artikel Testing'
        ]);
    }
}


