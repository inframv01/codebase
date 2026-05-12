<?php

namespace App\Services;

use App\Models\EmailOtp;
use App\Notifications\OtpCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class OtpService
{
    private const MAX_VERIFY_ATTEMPTS = 5;

    public function issue(string $email): void
    {
        $recentAttempts = EmailOtp::query()
            ->where('email', $email)
            ->where('created_at', '>=', now()->subHour())
            ->count();

        if ($recentAttempts >= 5) {
            throw ValidationException::withMessages([
                'email' => 'Too many OTP requests. Please try again later.',
            ]);
        }

        $code = (string) random_int(100000, 999999);

        EmailOtp::query()->create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
        ]);

        Notification::route('mail', $email)->notify(new OtpCodeNotification($code));
    }

    public function verify(string $email, string $code): void
    {
        $otp = EmailOtp::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->where('attempts', '<', self::MAX_VERIFY_ATTEMPTS)
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if ($otp === null) {
            throw ValidationException::withMessages([
                'code' => 'No valid OTP was found for this email address.',
            ]);
        }

        if (! Hash::check($code, $otp->code_hash)) {
            $otp->increment('attempts');

            if ((int) $otp->attempts >= self::MAX_VERIFY_ATTEMPTS) {
                $otp->forceFill([
                    'consumed_at' => Carbon::now(),
                ])->save();
            }

            throw ValidationException::withMessages([
                'code' => 'The provided OTP is invalid.',
            ]);
        }

        $otp->forceFill([
            'consumed_at' => Carbon::now(),
        ])->save();
    }
}
