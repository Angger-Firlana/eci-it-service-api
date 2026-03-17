<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorEnvelopeTest extends TestCase
{
    /** @test */
    public function it_returns_consistent_json_for_404(): void
    {
        $response = $this->getJson('/api/this-route-does-not-exist');

        $response->assertStatus(404)->assertJson([
            'success' => false,
            'code' => 404,
            'message' => 'Data Not Found',
        ]);
    }

    /** @test */
    public function it_returns_consistent_json_for_validation_errors(): void
    {
        $response = $this->postJson('/api/auth/login', []);

        $response
            ->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 422,
                'message' => 'Validation Error',
            ])
            ->assertJsonStructure([
                'errors' => ['email', 'password'],
            ]);
    }

    /** @test */
    public function it_returns_consistent_json_for_unauthenticated_requests(): void
    {
        $response = $this->getJson('/api/device-type');

        $response->assertStatus(401)->assertJson([
            'success' => false,
            'code' => 401,
            'message' => 'Unauthenticated',
        ]);
    }
}

