<?php

/**
 * API Authentication Tests
 *
 * Tests for authentication endpoints:
 * - User registration
 * - User login
 * - Token generation
 * - Session validation
 */

use App\Models\Island;
use App\Models\User;
use Database\Seeders\AtollSeeder;
use Database\Seeders\IslandSeeder;

describe('Auth API', function () {
    beforeEach(function () {
        $this->seed([AtollSeeder::class, IslandSeeder::class]);
    });

    describe('Registration', function () {
        it('should register a new customer', function () {
            $island = Island::query()->firstOrFail();

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'John Doe',
                'id_card_number' => 'A1234567',
                'atoll_id' => $island->atoll_id,
                'island_id' => $island->id,
                'house_name' => 'Rose Villa',
                'floor' => '2',
                'contact_numbers' => ['+9601234567'],
                'email' => 'john@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertCreated()
                ->assertJson([
                    'message' => 'Registration successful. Verify the OTP sent to your email address.',
                ]);

            $this->assertDatabaseHas('users', [
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ]);
        });

        it('should validate required fields on registration', function () {
            $response = $this->postJson('/api/v1/auth/register', []);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors([
                    'name',
                    'id_card_number',
                    'atoll_id',
                    'island_id',
                    'house_name',
                    'floor',
                    'contact_numbers',
                    'email',
                    'password',
                ]);
        });

        it('should reject invalid email', function () {
            $island = Island::query()->firstOrFail();

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'John Doe',
                'id_card_number' => 'A1234567',
                'atoll_id' => $island->atoll_id,
                'island_id' => $island->id,
                'house_name' => 'Rose Villa',
                'floor' => '2',
                'contact_numbers' => ['+9601234567'],
                'email' => 'invalid-email',
                'password' => 'Password123!',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('should reject weak password', function () {
            $island = Island::query()->firstOrFail();

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'John Doe',
                'id_card_number' => 'A1234567',
                'atoll_id' => $island->atoll_id,
                'island_id' => $island->id,
                'house_name' => 'Rose Villa',
                'floor' => '2',
                'contact_numbers' => ['+9601234567'],
                'email' => 'john@example.com',
                'password' => '123456',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['password']);
        });

        it('should reject duplicate email', function () {
            $island = Island::query()->firstOrFail();

            User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson('/api/v1/auth/register', [
                'name' => 'John Doe',
                'id_card_number' => 'A1234567',
                'atoll_id' => $island->atoll_id,
                'island_id' => $island->id,
                'house_name' => 'Rose Villa',
                'floor' => '2',
                'contact_numbers' => ['+9601234567'],
                'email' => 'existing@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('Login', function () {
        it('should login with valid credentials', function () {
            $user = User::factory()->create([
                'email' => 'john@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('Password123!'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'john@example.com',
                'password' => 'Password123!',
            ]);

            $response->assertSuccessful()
                ->assertJsonStructure([
                    'message',
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                    ],
                ])
                ->assertJsonPath('user.id', $user->id)
                ->assertJsonPath('message', 'Login successful.');
        });

        it('should reject invalid email', function () {
            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'nonexistent@example.com',
                'password' => 'password',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('should reject invalid password', function () {
            User::factory()->create([
                'email' => 'john@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('CorrectPassword123!'),
            ]);

            $response = $this->postJson('/api/v1/auth/login', [
                'email' => 'john@example.com',
                'password' => 'WrongPassword123!',
            ]);

            $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
        });

        it('should validate required fields', function () {
            $response = $this->postJson('/api/v1/auth/login', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'password']);
        });
    });
});
