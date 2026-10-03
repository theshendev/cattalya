<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:200'],
            'phone' => ['required', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'first_name.required' => 'لطفاً نام خود را وارد کنید.',
            'last_name.required' => 'لطفاً نام‌خانوادگی خود را وارد کنید.',
            'email.required' => 'لطفاً ایمیل خود را وارد کنید.',
            'email.email' => 'فرمت ایمیل صحیح نیست.',
            'phone.required' => 'لطفاً شماره تماس خود را وارد کنید.',
        ]);

        ContactMessage::create($data);

        return redirect()->route('contact')->with('success', 'پیام شما با موفقیت ثبت شد. به زودی با شما تماس می‌گیریم.');
    }
}
