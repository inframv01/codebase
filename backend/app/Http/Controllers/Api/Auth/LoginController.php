<?php

namespace App\Http\Controllers\Api\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $payload = $request->validated();
        $user = User::query()->where('email', $payload['email'])->first();

        if ($user === null || $user->password === null || ! Hash::check($payload['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => 'Verify your email address before signing in.',
            ]);
        }

        $token = $user->createToken('auth-token', $this->tokenAbilities($user))->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
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
