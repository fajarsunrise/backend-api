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
    $user = User::factory()->create();

    // Buat 1 kategori palsu untuk pengujian
    $category = \App\Models\Category::create(['nama_kategori' => 'Teknologi']);

    // Siapkan payload dengan menyertakan category_id
    $payload = [
        'judul' => 'Judul Artikel Testing',
        'konten' => 'Ini adalah isi dari artikel testing otomatis.',
        'category_id' => $category->id
    ];

    // 2. Tindakan (Act)
    $response = $this->actingAs($user, 'sanctum')
                     ->postJson('/api/articles', $payload);

    // 3. Verifikasi (Assert)
    $response->assertStatus(201);

    // Pastikan database menyimpan artikel beserta relasi kategorinya
    $this->assertDatabaseHas('articles', [
        'judul' => 'Judul Artikel Testing',
        'category_id' => $category->id
    ]);
    }

    public function test_user_yang_sudah_login_bisa_mengubah_artikel(): void
    {
        // 1. Persiapan: Buat user dan 1 artikel awal di database
        $user = User::factory()->create();
        $article = Article::create([
            'judul' => 'Judul Lama',
            'konten' => 'Isi konten lama'
        ]);

        // Siapkan data perubahan
        $payload = [
            'judul' => 'Judul Telah Diperbarui'
        ];

        // 2. Tindakan: User login dan mengirim PUT request ke ID artikel tersebut
        $response = $this->actingAs($user, 'sanctum')
                         ->putJson('/api/articles/' . $article->id, $payload);

        // 3. Verifikasi: Status 200 OK dan judul di database benar-benar berubah
        $response->assertStatus(200);
        $this->assertDatabaseHas('articles', [
            'id' => $article->id,
            'judul' => 'Judul Telah Diperbarui'
        ]);
    }

    public function test_user_yang_sudah_login_bisa_menghapus_artikel(): void
    {
        // 1. Persiapan: Buat user dan 1 artikel
        $user = User::factory()->create();
        $article = Article::create([
            'judul' => 'Artikel Untuk Dihapus',
            'konten' => 'Konten akan hilang'
        ]);

        // 2. Tindakan: User login dan mengirim DELETE request
        $response = $this->actingAs($user, 'sanctum')
                         ->deleteJson('/api/articles/' . $article->id);

        // 3. Verifikasi: Status 200 OK dan data sudah tidak ada di database
        $response->assertStatus(200);
        $this->assertDatabaseMissing('articles', [
            'id' => $article->id
        ]);
    }
}


