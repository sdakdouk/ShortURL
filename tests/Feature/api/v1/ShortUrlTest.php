<?php

namespace Tests\Feature\api\v1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShortUrlTest extends TestCase
{
    /**
     * Test a valid token and URL.
     */
    public function test_short_url_with_valid_token_and_url()
    {
        $response = $this->postJson('/api/v1/short-urls', [
            'url' => 'https://www.youtube.com/watch?v=jq8IUoP4diE&ab_channel=Tugatocurioso',
        ], [
            'Authorization' => 'Bearer []{}()',
            'Accept' => 'application/json',

        ]);

        //$response->dump();

        $response->assertStatus(200)
                 ->assertJsonStructure(['url']);
    }

    /**
     * Test an invalid token.
     */
    public function test_short_url_with_invalid_token()
    {
        $response = $this->postJson('/api/v1/short-urls', [
            'url' => 'https://www.youtube.com/watch?v=jq8IUoP4diE&ab_channel=Tugatocurioso',
        ], [
            'Authorization' => 'Bearer [{]}(}[}',
            'Accept' => 'application/json',

        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }

    /**
     * Test without token to see if it cancel the petition.
     */
    public function test_short_url_without_token()
    {
        $response = $this->postJson('/api/v1/short-urls', [
            'url' => 'https://www.youtube.com/watch?v=jq8IUoP4diE&ab_channel=Tugatocurioso',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Unauthorized']);
    }

    /**
     * Test an invalid URL.
     */
    public function test_short_url_with_invalid_url()
    {
        $response = $this->postJson('/api/v1/short-urls', [
            'url' => 'invalid-url',
        ], [
            'Authorization' => 'Bearer []{}()',
            'Accept' => 'application/json',

        ]);
        
        //$response->dump();
        
        $response->assertStatus(422) 
                 ->assertJsonValidationErrors(['url']);
    }
}
