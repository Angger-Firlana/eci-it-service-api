<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ErrorEnvelopeTest extends TestCase
{
    #[Test]
    public function it_returns_consistent_json_for_404(): void
    {
        $response = $this->getJson('/api/this-route-does-not-exist');

        $response->assertStatus(404)->assertJson([
            'success' => false,
            'code' => 404,
            'message' => 'Data Not Found',
        ]);
    }

    #[Test]
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

    #[Test]
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
