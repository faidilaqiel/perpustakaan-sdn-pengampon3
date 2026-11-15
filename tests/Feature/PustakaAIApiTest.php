<?php

namespace Tests\Feature;

use Tests\TestCase;

class PustakaAIApiTest extends TestCase
{
    /**
     * Test endpoint API chatbot PustakaAI merespons dengan format JSON
     */
    public function test_chatbot_endpoint_returns_json_response(): void
    {
        $response = $this->postJson('/api/chatbot', [
            'message' => 'Halo PustakaAI',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['reply']);
    }

    /**
     * Test endpoint info perpustakaan mengembalikan data valid
     */
    public function test_library_info_endpoint_returns_success(): void
    {
        $response = $this->get('/informasi-perpustakaan');
        $response->assertStatus(200);
    }
}

