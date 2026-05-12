<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, OtpService $otpService): JsonResponse
    {
        $payload = $request->validated();

        DB::transaction(function () use ($payload, $otpService): void {
            $user = User::query()->create([
                'name' => $payload['name'],
                'id_card_number' => $payload['id_card_number'],
                'atoll_id' => $payload['atoll_id'],
                'island_id' => $payload['island_id'],
                'house_name' => $payload['house_name'],
                'floor' => $payload['floor'],
                'email' => $payload['email'],
                'password' => $payload['password'],
                'role' => 'customer',
            ]);

            $user->contactNumbers()->createMany(
                collect($payload['contact_numbers'])
                    ->values()
                    ->map(fn (string $number, int $index) => [
                        'number' => $number,
                        'position' => $index + 1,
                    ])
                    ->all()
            );

            $otpService->issue($user->email);
        });

        return response()->json([
            'message' => 'Registration successful. Verify the OTP sent to your email address.',
        ], 201);
    }
}
