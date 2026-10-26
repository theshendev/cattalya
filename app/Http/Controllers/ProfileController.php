<?php

namespace App\Http\Controllers;

use App\Services\OtpService;
use App\Support\Jalali;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const NEW_PHONE_KEY = 'profile.new_phone';

    public function __construct(private OtpService $otp) {}

    public function show(Request $request): View
    {
        return view('profile.show', [
            'user' => $request->user(),
            'pendingPhone' => $request->session()->get(self::NEW_PHONE_KEY),
        ]);
    }

    public function updateIdentity(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:160'],
            'national_id' => ['nullable', 'digits:10', Rule::unique('users')->ignore($user->id)],
            'birth_date' => ['nullable', 'string', 'max:10', function (string $attribute, mixed $value, \Closure $fail) {
                $date = Jalali::parse((string) $value);

                if (! $date || $date->isFuture()) {
                    $fail('تاریخ تولد معتبر نیست. نمونه: 1379/01/28');
                }
            }],
        ], [
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'national_id.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'national_id.unique' => 'این کد ملی قبلاً ثبت شده است.',
        ]);

        $birthDate = filled($data['birth_date'] ?? null) ? Jalali::parse($data['birth_date']) : null;

        [$firstName, $lastName] = array_pad(preg_split('/\s+/u', trim($data['full_name']), 2), 2, null);

        $user->update([
            'name' => $firstName,
            'last_name' => $lastName,
            'national_id' => $data['national_id'] ?? null,
            'birth_date' => $birthDate,
        ]);

        return back()->with('success', 'اطلاعات شناسایی ذخیره شد.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => [$user->hasPassword() ? 'required' : 'nullable', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'رمز عبور فعلی را وارد کنید.',
            'password.required' => 'رمز عبور جدید را وارد کنید.',
            'password.confirmed' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'password.min' => 'رمز عبور باید حداقل ۸ کاراکتر باشد.',
        ]);

        if ($user->hasPassword() && ! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages(['current_password' => 'رمز عبور فعلی اشتباه است.']);
        }

        $user->update(['password' => $data['password'], 'password_set_at' => now()]);

        return back()->with('success', 'رمز عبور تغییر کرد.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
        ], [
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'email.unique' => 'این ایمیل قبلاً ثبت شده است.',
        ]);

        $user->update(['email' => $data['email'] ?? null]);

        return back()->with('success', 'ایمیل ذخیره شد.');
    }

    /**
     * Step 1 of changing the phone number: send a code to the new number.
     */
    public function sendPhoneOtp(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'phone' => ['required', 'regex:/^09[0-9]{9}$/', Rule::unique('users')->ignore($user->id)],
        ], [
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'phone.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
        ]);

        if ($data['phone'] === $user->phone) {
            return back()->withErrors(['phone' => 'این شماره همان شماره فعلی شماست.']);
        }

        if ($error = $this->otp->send($data['phone'])) {
            return back()->withErrors(['phone' => $error]);
        }

        $request->session()->put(self::NEW_PHONE_KEY, $data['phone']);

        return back()->with('open_modal', 'phone-verify');
    }

    /**
     * Step 2: confirm the code and switch the number.
     */
    public function verifyPhone(Request $request): RedirectResponse
    {
        $data = $request->validate(['otp' => ['required', 'digits:6']], [
            'otp.required' => 'کد تایید را وارد کنید.',
            'otp.digits' => 'کد تایید باید ۶ رقم باشد.',
        ]);

        $phone = $request->session()->get(self::NEW_PHONE_KEY);

        if (! $phone) {
            return back()->withErrors(['phone' => 'ابتدا شماره جدید را وارد کنید.']);
        }

        if ($error = $this->otp->verify($phone, $data['otp'])) {
            return back()->withErrors(['otp' => $error])->with('open_modal', 'phone-verify');
        }

        $request->user()->update(['phone' => $phone]);
        $request->session()->forget(self::NEW_PHONE_KEY);

        return back()->with('success', 'شماره تماس تغییر کرد.');
    }
}
