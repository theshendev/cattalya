<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ─── Show login/register form ───────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    // ─── Step 1: Send OTP ───────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
        ], [
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
        ]);

        $phone = $request->phone;
        $otp = random_int(10000, 99999);

        Cache::put("otp:{$phone}", $otp, now()->addMinutes(5));

        // Send via Kavenegar if key is set, else log for dev
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

        return back()->with([
            'otp_sent' => true,
            'phone' => $phone,
        ]);
    }

    // ─── Step 2: Verify OTP & Login/Register ────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/'],
            'otp' => ['required', 'digits:5'],
        ]);

        $phone = $request->phone;
        $cachedOtp = Cache::get("otp:{$phone}");

        if (! $cachedOtp || (string) $cachedOtp !== $request->otp) {
            return back()->withErrors(['otp' => 'کد وارد شده اشتباه یا منقضی شده است.'])
                ->with(['otp_sent' => true, 'phone' => $phone]);
        }

        Cache::forget("otp:{$phone}");

        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'کاربر '.substr($phone, -4), 'password' => Hash::make(Str::random(32))],
        );

        Auth::login($user, remember: true);

        return redirect()->intended(route('home'))->with('success', 'خوش آمدید!');
    }

    // ─── Logout ─────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
