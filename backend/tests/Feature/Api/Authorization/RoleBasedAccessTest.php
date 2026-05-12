<?php

/**
 * API Authorization Tests
 *
 * Tests for authorization (role-based access control):
 * - Customer endpoint access
 * - Operator endpoint access
 * - Permission validation
 * - Token authentication
 */

use App\Models\User;
use Laravel\Sanctum\Sanctum;

describe('Authorization API', function () {
    describe('Unauthenticated Access', function () {
        it('should require authentication for protected endpoints', function () {
            $response = $this->getJson('/api/v1/me');

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        });

        it('should reject invalid tokens', function () {
            $response = $this->getJson('/api/v1/me', [
                'Authorization' => 'Bearer invalid-token-xyz',
            ]);

            $response->assertUnauthorized();
        });
    });

    describe('Customer Access', function () {
        it('should allow customer to access own profile', function () {
            $user = User::factory()->customer()->create();

            Sanctum::actingAs($user, ['customer']);

            $response = $this->getJson('/api/v1/me');

            $response->assertSuccessful()
                ->assertJsonPath('id', $user->id)
                ->assertJsonPath('email', $user->email);
        });

        it('should deny customer access to operator endpoints', function () {
            $user = User::factory()->customer()->create();

            Sanctum::actingAs($user, ['customer']);

            $response = $this->getJson('/api/v1/operator/atolls');

            $response->assertForbidden();
        });

        it('should allow customer to create delivery requests', function () {
            $user = User::factory()->customer()->create();

            Sanctum::actingAs($user, ['customer']);

            $response = $this->postJson('/api/v1/delivery-requests', []);

            $response->assertUnprocessable();
        });
    });

    describe('Operator Access', function () {
        it('should deny non-operator access to operator endpoints', function () {
            $user = User::factory()->customer()->create();

            Sanctum::actingAs($user, ['customer']);

            $response = $this->getJson('/api/v1/operator/atolls');

            $response->assertForbidden();
        });

        it('should allow operator to access operator endpoints', function () {
            $user = User::factory()->operator()->create();

            Sanctum::actingAs($user, ['operator']);

            $response = $this->getJson('/api/v1/operator/atolls');

            $response->assertSuccessful();
        });

        it('should allow admin to access operator endpoints', function () {
            $user = User::factory()->admin()->create();

            Sanctum::actingAs($user, ['operator']);

            $response = $this->getJson('/api/v1/operator/atolls');

            $response->assertSuccessful();
        });
    });

    describe('Token Expiration & Revocation', function () {
        it('should accept requests with valid token', function () {
            $user = User::factory()->customer()->create();
            $token = $user->createToken('test-token')->plainTextToken;

            $response = $this->getJson('/api/v1/me', [
                'Authorization' => "Bearer {$token}",
            ]);

            $response->assertStatus(200);
        });
    });
});
