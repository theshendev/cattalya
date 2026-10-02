<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'last_name' => ['nullable', 'string', 'max:80'],
            'phone' => ['required', 'regex:/^09[0-9]{9}$/', Rule::unique('users')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'national_id' => ['nullable', 'digits:10', Rule::unique('users')->ignore($user->id)],
            'birth_date' => ['nullable', 'date', 'before:today'],
        ], [
            'name.required' => 'نام الزامی است.',
            'phone.required' => 'شماره موبایل الزامی است.',
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'phone.unique' => 'این شماره موبایل قبلاً ثبت شده است.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'national_id.digits' => 'کد ملی باید ۱۰ رقم باشد.',
            'birth_date.before' => 'تاریخ تولد باید قبل از امروز باشد.',
        ]);

        $user->update($validated);

        return back()->with('success', 'اطلاعات شخصی شما به‌روزرسانی شد.');
    }
}
