<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class OtpService
{
    public const RESEND_SECONDS = 120;

    private const TTL_MINUTES = 5;

    private const MAX_ATTEMPTS = 5;

    /**
     * Send a fresh code to the phone number.
     *
     * @return string|null An error message, or null when the code was sent.
     */
    public function send(string $phone): ?string
    {
        $remaining = $this->secondsUntilResend($phone);

        if ($remaining > 0) {
            return "برای دریافت دوباره کد {$remaining} ثانیه صبر کنید.";
        }

        $otp = random_int(100000, 999999);

        Cache::put($this->codeKey($phone), $otp, now()->addMinutes(self::TTL_MINUTES));
        Cache::put($this->cooldownKey($phone), now()->addSeconds(self::RESEND_SECONDS)->timestamp, self::RESEND_SECONDS);
        RateLimiter::clear($this->attemptsKey($phone));

        $apiKey = config('services.kavenegar.api_key');

        if ($apiKey) {
            Http::timeout(10)->get("https://api.kavenegar.com/v1/{$apiKey}/verify/lookup.json", [
                'receptor' => $phone,
                'token' => $otp,
                'template' => 'cattalya-otp',
            ]);
        } else {
            logger("OTP for {$phone}: {$otp}");
        }

        return null;
    }

    /**
     * Check a code the buyer typed in. A correct code is consumed.
     *
     * @return string|null An error message, or null when the code is valid.
     */
    public function verify(string $phone, string $code): ?string
    {
        if (RateLimiter::tooManyAttempts($this->attemptsKey($phone), self::MAX_ATTEMPTS)) {
            return 'تعداد تلاش‌های ناموفق زیاد بود. چند دقیقه بعد دوباره تلاش کنید.';
        }

        $expected = Cache::get($this->codeKey($phone));

        if ($expected === null || ! hash_equals((string) $expected, $code)) {
            RateLimiter::hit($this->attemptsKey($phone), self::TTL_MINUTES * 60);

            return 'کد وارد شده اشتباه یا منقضی شده است.';
        }

        Cache::forget($this->codeKey($phone));
        RateLimiter::clear($this->attemptsKey($phone));

        return null;
    }

    public function secondsUntilResend(string $phone): int
    {
        $availableAt = Cache::get($this->cooldownKey($phone));

        return $availableAt ? max(0, $availableAt - now()->timestamp) : 0;
    }

    private function codeKey(string $phone): string
    {
        return "otp:{$phone}";
    }

    private function cooldownKey(string $phone): string
    {
        return "otp-cooldown:{$phone}";
    }

    private function attemptsKey(string $phone): string
    {
        return "otp-attempts:{$phone}";
    }
}
