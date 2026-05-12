<?php

use App\Models\EmailOtp;
use App\Models\Island;
use App\Models\User;
use App\Notifications\OtpCodeNotification;
use Database\Seeders\AtollSeeder;
use Database\Seeders\IslandSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('a user can register and receive an otp notification', function () {
    Notification::fake();
    $this->seed([AtollSeeder::class, IslandSeeder::class]);
    $island = Island::query()->firstOrFail();

    $response = $this->postJson('/api/v1/auth/register', [
        'name' => 'Ahmed Ali',
        'id_card_number' => 'A9988776',
        'atoll_id' => $island->atoll_id,
        'island_id' => $island->id,
        'house_name' => 'Beach House',
        'floor' => '2',
        'contact_numbers' => ['+9607000001', '+9607000002'],
        'email' => 'ahmed@example.com',
        'password' => 'password123',
    ]);

    $response->assertCreated()->assertJsonPath('message', 'Registration successful. Verify the OTP sent to your email address.');

    $user = User::query()->where('email', 'ahmed@example.com')->first();

    expect($user)->not->toBeNull();
    expect($user->contactNumbers)->toHaveCount(2);

    Notification::assertSentOnDemand(OtpCodeNotification::class);
});

test('a user can verify otp and receive a sanctum token', function () {
    $this->seed([AtollSeeder::class, IslandSeeder::class]);
    $island = Island::query()->firstOrFail();

    $user = User::factory()->unverified()->create([
        'email' => 'verify@example.com',
        'atoll_id' => $island->atoll_id,
        'island_id' => $island->id,
    ]);

    EmailOtp::query()->create([
        'email' => $user->email,
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    $response = $this->postJson('/api/v1/auth/otp/verify', [
        'email' => $user->email,
        'code' => '123456',
    ]);

    $response->assertSuccessful()->assertJsonStructure(['message', 'token', 'user']);

    expect($user->fresh()->email_verified_at)->not->toBeNull();
});

test('otp becomes invalid after too many failed verification attempts', function () {
    $this->seed([AtollSeeder::class, IslandSeeder::class]);
    $island = Island::query()->firstOrFail();

    $user = User::factory()->unverified()->create([
        'email' => 'lockout@example.com',
        'atoll_id' => $island->atoll_id,
        'island_id' => $island->id,
    ]);

    EmailOtp::query()->create([
        'email' => $user->email,
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
    ]);

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/auth/otp/verify', [
            'email' => $user->email,
            'code' => '000000',
        ])->assertUnprocessable();
    }

    $this->postJson('/api/v1/auth/otp/verify', [
        'email' => $user->email,
        'code' => '123456',
    ])->assertUnprocessable();

    expect(EmailOtp::query()->latest('id')->firstOrFail()->consumed_at)->not->toBeNull();
});
