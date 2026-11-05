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
    // 1. Persiapan Data
    $user = User::factory()->create();
    $category = \App\Models\Category::create(['nama_kategori' => 'Teknologi']);

    // Buat 2 tag palsu
    $tag1 = \App\Models\Tag::create(['nama_tag' => 'Laravel']);
    $tag2 = \App\Models\Tag::create(['nama_tag' => 'Tutorial']);

    // Siapkan payload dengan menyertakan category_id dan array tags
    $payload = [
        'judul' => 'Judul Artikel Many-to-Many',
        'konten' => 'Isi konten ini terhubung ke kategori dan banyak tag.',
        'category_id' => $category->id,
        'tags' => [$tag1->id, $tag2->id] // Mengirimkan 2 ID tag sekaligus
    ];

    // 2. Tindakan
    $response = $this->actingAs($user, 'sanctum')
                     ->postJson('/api/articles', $payload);

    // 3. Verifikasi
    $response->assertStatus(201);

    // Cek tabel articles utama
    $this->assertDatabaseHas('articles', [
        'judul' => 'Judul Artikel Many-to-Many',
        'category_id' => $category->id
    ]);

    // AMAT PENTING: Cek pivot table (article_tag) untuk memastikan relasi tersimpan!
    // Karena response JSON mengembalikan id artikel yang baru dibuat di 'data.id_artikel'
    $articleId = $response->json('data.id_artikel');

    $this->assertDatabaseHas('article_tag', [
        'article_id' => $articleId,
        'tag_id' => $tag1->id
    ]);

    $this->assertDatabaseHas('article_tag', [
        'article_id' => $articleId,
        'tag_id' => $tag2->id
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

    public function test_user_bisa_mencari_artikel_berdasarkan_judul_atau_konten(): void
{
    $user = User::factory()->create();
    $category = \App\Models\Category::create(['nama_kategori' => 'Teknologi']);

    // Buat 2 artikel dengan judul berbeda
    \App\Models\Article::create(['judul' => 'Belajar Laravel API', 'konten' => 'Isi konten API', 'category_id' => $category->id]);
    \App\Models\Article::create(['judul' => 'Tutorial React JS', 'konten' => 'Isi konten React', 'category_id' => $category->id]);

    // Lakukan pencarian dengan kata kunci 'Laravel'
    $response = $this->actingAs($user, 'sanctum')
                     ->getJson('/api/articles?search=Laravel');

    $response->assertStatus(200);

    // Pastikan hanya 1 artikel yang dikembalikan (Laravel API)
    $response->assertJsonCount(1, 'data');
    $this->assertEquals('Belajar Laravel API', $response->json('data.0.judul_artikel'));
}

public function test_format_pagination_berjalan_dengan_benar(): void
{
    $user = User::factory()->create();
    $category = \App\Models\Category::create(['nama_kategori' => 'Umum']);

    // Buat 15 artikel secara massal menggunakan perulangan
    for ($i = 1; $i <= 15; $i++) {
        \App\Models\Article::create([
            'judul' => "Artikel ke-$i",
            'konten' => 'Konten testing',
            'category_id' => $category->id
        ]);
    }

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/articles');

    $response->assertStatus(200);

    // Karena paginate(10), maka array 'data' hanya boleh berisi 10 item di halaman pertama
    $response->assertJsonCount(10, 'data');

    // Pastikan struktur JSON memiliki informasi meta dan links (tanda pagination aktif)
    $response->assertJsonStructure([
        'data',
        'links' => ['first', 'last', 'prev', 'next'],
        'meta' => ['current_page', 'last_page', 'per_page', 'total']
    ]);
}

public function test_user_bisa_mengunggah_gambar_saat_membuat_artikel(): void
{
    // PENTING: Gunakan storage palsu agar tidak mengotori folder asli saat testing
    \Illuminate\Support\Facades\Storage::fake('public');

    $user = \App\Models\User::factory()->create();

    // Buat satu file gambar tiruan berukuran 1MB (1024 KB)
    $gambarPalsu = \Illuminate\Http\UploadedFile::fake()->image('sampul.jpg')->size(1024);

    $payload = [
        'judul' => 'Artikel dengan Gambar',
        'konten' => 'Ini kontennya.',
        'gambar_sampul' => $gambarPalsu
    ];

    // Karena kita mengirim file, kita tidak bisa menggunakan postJson().
    // Kita harus mengirim HTTP request biasa dengan format form-data.
    $response = $this->actingAs($user, 'sanctum')
                     ->post('/api/articles', $payload, [
                         'Accept' => 'application/json' // Memaksa respon kembalian berupa JSON
                     ]);

    $response->assertStatus(201);

    // Pastikan nama file tersimpan di database
    $this->assertDatabaseHas('articles', [
        'judul' => 'Artikel dengan Gambar'
    ]);

    // Ambil nama file dari database
    $artikel = \App\Models\Article::where('judul', 'Artikel dengan Gambar')->first();

    // Pastikan file tersebut benar-benar ada di dalam storage palsu
    \Illuminate\Support\Facades\Storage::disk('public')->assertExists($artikel->gambar_sampul);
}
}


