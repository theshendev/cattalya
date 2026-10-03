<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    private const PHONE_RULE = ['required', 'regex:/^09[0-9]{9}$/'];

    private const PHONE_MESSAGES = [
        'phone.required' => 'شماره موبایل الزامی است.',
        'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
    ];

    public function __construct(private OtpService $otp) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function attemptLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'phone' => self::PHONE_RULE,
            'password' => ['required', 'string'],
        ], self::PHONE_MESSAGES + ['password.required' => 'رمز عبور الزامی است.']);

        $throttleKey = 'login:'.Str::lower($credentials['phone']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withInput($request->only('phone'))
                ->withErrors(['phone' => 'تلاش‌های ناموفق زیاد بود. چند دقیقه بعد دوباره تلاش کنید.']);
        }

        $user = User::where('phone', $credentials['phone'])->first();

        if (! $user || ! $user->hasPassword() || ! Auth::attempt($credentials, remember: true)) {
            RateLimiter::hit($throttleKey, 300);

            return back()->withInput($request->only('phone'))
                ->withErrors(['phone' => 'شماره موبایل یا رمز عبور اشتباه است. اگر رمز عبور ندارید با کد یکبار مصرف وارد شوید.']);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'خوش آمدید!');
    }

    /**
     * Send a one-time code, for both registration and passwordless login.
     */
    public function sendOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['phone' => self::PHONE_RULE], self::PHONE_MESSAGES);

        if ($error = $this->otp->send($data['phone'])) {
            // A resend inside the cooldown keeps the buyer on the code screen.
            return $request->session()->get('auth.phone') === $data['phone']
                ? redirect()->route('otp.show')->withErrors(['otp' => $error])
                : back()->withInput()->withErrors(['phone' => $error]);
        }

        $request->session()->put('auth.phone', $data['phone']);
        $request->session()->forget('auth.verified_phone');

        return redirect()->route('otp.show');
    }

    public function showVerify(Request $request): View|RedirectResponse
    {
        $phone = $request->session()->get('auth.phone');

        if (! $phone) {
            return redirect()->route('register');
        }

        return view('auth.verify', [
            'phone' => $phone,
            'resendIn' => $this->otp->secondsUntilResend($phone),
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']], [
            'otp.required' => 'کد تایید را وارد کنید.',
            'otp.digits' => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $phone = $request->session()->get('auth.phone');

        if (! $phone) {
            return redirect()->route('register');
        }

        if ($error = $this->otp->verify($phone, $data['otp'])) {
            return redirect()->route('otp.show')->withErrors(['otp' => $error]);
        }

        $user = User::where('phone', $phone)->first();

        if ($user) {
            $request->session()->forget('auth.phone');
            Auth::login($user, remember: true);
            $request->session()->regenerate();

            return redirect()->intended(route('home'))->with('success', 'خوش آمدید!');
        }

        $request->session()->put('auth.verified_phone', $phone);

        return redirect()->route('register.complete');
    }

    public function showComplete(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('auth.verified_phone')) {
            return redirect()->route('register');
        }

        return view('auth.complete');
    }

    public function completeRegistration(Request $request): RedirectResponse
    {
        $phone = $request->session()->get('auth.verified_phone');

        if (! $phone) {
            return redirect()->route('register');
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'national_id' => ['nullable', 'digits:10', 'unique:users,national_id'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'national_id.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_id.unique' => 'این کد ملی قبلاً ثبت شده است.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ]);

        [$firstName, $lastName] = array_pad(preg_split('/\s+/u', trim($data['full_name']), 2), 2, null);

        $user = User::create([
            'name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'national_id' => $data['national_id'] ?? null,
            'password' => $data['password'],
            'password_set_at' => now(),
        ]);

        $request->session()->forget(['auth.phone', 'auth.verified_phone']);
        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'حساب کاربری شما ساخته شد.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
