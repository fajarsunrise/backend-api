<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_bisa_membuat_tag(): void
    {
        $user = User::factory()->create();
        $payload = ['nama_tag' => 'Laravel'];

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson('/api/tags', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tags', ['nama_tag' => 'Laravel']);
    }

    public function test_user_bisa_melihat_daftar_tag(): void
    {
        $user = User::factory()->create();
        Tag::create(['nama_tag' => 'PHP']);
        Tag::create(['nama_tag' => 'API']);

        $response = $this->actingAs($user, 'sanctum')
                         ->getJson('/api/tags');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }
}
