<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')->stateless()->redirect()->getTargetUrl(),
        ]);
    }

    public function callback(): JsonResponse
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = DB::transaction(function () use ($googleUser): User {
            $existingUser = User::query()
                ->where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if ($existingUser !== null) {
                $existingUser->forceFill([
                    'name' => $googleUser->getName() ?: $existingUser->name,
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => $existingUser->email_verified_at ?? now(),
                ])->save();

                return $existingUser;
            }

            return User::query()->create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'email_verified_at' => now(),
                'password' => null,
                'role' => 'customer',
            ]);
        });

        $token = $user->createToken('auth-token', ['customer'])->plainTextToken;

        return response()->json([
            'message' => 'Google authentication successful.',
            'token' => $token,
            'user' => $user->load('contactNumbers'),
        ]);
    }
}
