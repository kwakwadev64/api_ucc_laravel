<?php

namespace Tests\Feature;

use App\Models\Actualite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActualitesEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_endpoints_return_every_record_without_authentication(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            Actualite::create([
                'titre' => "Actualite $i",
                'location' => 'Campus',
                'description' => 'Description',
                'image_url' => "actualites/$i.jpg",
                'is_published' => $i !== 5,
            ]);
        }

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/actualites')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertJsonCount(5)
            ->assertJsonFragment(['titre' => 'Actualite 5']);

        $this->withHeader('Origin', 'http://localhost:5173')
            ->getJson('/api/accueil-site')
            ->assertOk()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertJsonCount(5, 'actualites');
    }
}
