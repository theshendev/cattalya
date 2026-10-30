<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $this->validated($request);

        $address = $user->addresses()->create($data + [
            'receiver_name' => trim($user->name.' '.$user->last_name),
            'phone' => $user->phone,
        ]);

        if ($request->boolean('is_default') || $user->addresses()->count() === 1) {
            $this->makeDefault($address);
        }

        return back()->with('success', 'آدرس جدید ثبت شد.');
    }

    public function update(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $address->update($this->validated($request));

        if ($request->boolean('is_default')) {
            $this->makeDefault($address);
        }

        return back()->with('success', 'آدرس ویرایش شد.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault && $replacement = $request->user()->addresses()->first()) {
            $this->makeDefault($replacement);
        }

        return back()->with('success', 'آدرس حذف شد.');
    }

    /**
     * Validate the address fields. When the buyer says they are the receiver, their own
     * name and phone replace whatever receiver details were sent.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:50'],
            'receiver_is_self' => ['sometimes', 'boolean'],
            'receiver_name' => ['nullable', 'required_if:receiver_is_self,0', 'string', 'max:100'],
            'phone' => ['nullable', 'required_if:receiver_is_self,0', 'regex:/^09[0-9]{9}$/'],
            'province' => ['required', 'string', 'max:50'],
            'city' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:500'],
            'plaque' => ['nullable', 'string', 'max:20'],
            'unit' => ['nullable', 'string', 'max:20'],
            'postal_code' => ['required', 'digits:10'],
        ], [
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست.',
            'province.required' => 'استان الزامی است.',
            'city.required' => 'شهر الزامی است.',
            'address.required' => 'آدرس الزامی است.',
            'postal_code.required' => 'کد پستی الزامی است.',
            'postal_code.digits' => 'کد پستی باید ۱۰ رقم باشد.',
            'receiver_name.required_if' => 'نام و نام خانوادگی گیرنده الزامی است.',
            'phone.required_if' => 'شماره همراه گیرنده الزامی است.',
        ]);

        if ($request->boolean('receiver_is_self')) {
            $user = $request->user();
            $data['receiver_name'] = trim($user->name.' '.$user->last_name);
            $data['phone'] = $user->phone;
        }

        unset($data['receiver_is_self']);

        return array_filter($data, fn ($value, $field) => $value !== null || ! in_array($field, ['receiver_name', 'phone'], true), ARRAY_FILTER_USE_BOTH);
    }

    private function makeDefault(Address $address): void
    {
        Address::where('user_id', $address->user_id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }
}
