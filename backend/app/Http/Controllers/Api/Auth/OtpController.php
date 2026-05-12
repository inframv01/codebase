<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class OtpController extends Controller
{
    public function resend(ResendOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $user = User::query()->where('email', $request->validated('email'))->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'No account was found for this email address.',
            ]);
        }

        $otpService->issue($user->email);

        return response()->json([
            'message' => 'A new OTP has been sent to your email address.',
        ], 202);
    }

    public function verify(VerifyOtpRequest $request, OtpService $otpService): JsonResponse
    {
        $payload = $request->validated();
        $user = User::query()->where('email', $payload['email'])->first();

        if ($user === null) {
            throw ValidationException::withMessages([
                'email' => 'No account was found for this email address.',
            ]);
        }

        $otpService->verify($user->email, $payload['code']);

        if ($user->email_verified_at === null) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        $token = $user->createToken('auth-token', $this->tokenAbilities($user))->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => $user->load('contactNumbers'),
        ]);
    }

    private function tokenAbilities(User $user): array
    {
        return match ($user->role) {
            UserRole::Admin, UserRole::Operator => ['operator'],
            default => ['customer'],
        };
    }
}
