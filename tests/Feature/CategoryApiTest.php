<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_yang_sudah_login_bisa_membuat_kategori(): void
    {
        // 1. Persiapan
        $user = User::factory()->create();
        $payload = [
            'nama_kategori' => 'Teknologi'
        ];

        // 2. Tindakan
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/categories', $payload);

        // 3. Verifikasi
        $response->assertStatus(201);
        $this->assertDatabaseHas('categories', [
            'nama_kategori' => 'Teknologi'
        ]);
    }

    public function test_user_yang_sudah_login_bisa_melihat_daftar_kategori(): void
    {
        // 1. Persiapan
        $user = User::factory()->create();
        Category::create(['nama_kategori' => 'Kesehatan']);
        Category::create(['nama_kategori' => 'Bisnis']);

        // 2. Tindakan
        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/categories');

        // 3. Verifikasi
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
