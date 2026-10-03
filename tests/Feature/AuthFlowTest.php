<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_visitor_registers_with_a_code_then_completes_their_profile(): void
    {
        $this->post(route('otp.send'), ['phone' => '09121234567'])->assertRedirect(route('otp.show'));
        $this->get(route('otp.show'))->assertOk()->assertSeeText('تایید کد');

        $this->post(route('otp.verify'), ['otp' => (string) Cache::get('otp:09121234567')])
            ->assertRedirect(route('register.complete'));

        $this->post(route('register.store'), [
            'full_name' => 'پروانه سادات موسوی',
            'national_id' => '0012345678',
            'password' => 'secret-pass-1',
            'password_confirmation' => 'secret-pass-1',
        ])->assertRedirect(route('home'));

        $user = User::where('phone', '09121234567')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('پروانه', $user->name);
        $this->assertSame('سادات موسوی', $user->last_name);
        $this->assertTrue($user->hasPassword());
    }

    public function test_registration_cannot_skip_the_code_step(): void
    {
        $this->get(route('register.complete'))->assertRedirect(route('register'));
        $this->post(route('register.store'), ['full_name' => 'x', 'password' => 'secret-pass-1', 'password_confirmation' => 'secret-pass-1'])
            ->assertRedirect(route('register'));
        $this->assertGuest();
    }

    public function test_existing_user_can_log_in_with_a_code(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->post(route('otp.send'), ['phone' => '09121234567']);
        $this->post(route('otp.verify'), ['otp' => (string) Cache::get('otp:09121234567')])
            ->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_codes_are_rejected_and_lock_out_after_repeated_failures(): void
    {
        $this->post(route('otp.send'), ['phone' => '09121234567']);

        foreach (range(1, 5) as $ignored) {
            $this->post(route('otp.verify'), ['otp' => '000000'])->assertSessionHasErrors('otp');
        }

        // Even the right code is refused once the limit is hit.
        $this->post(route('otp.verify'), ['otp' => (string) Cache::get('otp:09121234567')])
            ->assertSessionHasErrors('otp');
        $this->assertGuest();
    }

    public function test_codes_cannot_be_resent_inside_the_cooldown(): void
    {
        $this->post(route('otp.send'), ['phone' => '09121234567']);
        $first = Cache::get('otp:09121234567');

        $this->post(route('otp.send'), ['phone' => '09121234567'])->assertSessionHasErrors('otp');
        $this->assertSame($first, Cache::get('otp:09121234567'));
    }

    public function test_user_can_log_in_with_phone_and_password(): void
    {
        $user = User::factory()->create(['phone' => '09121234567', 'password' => 'secret-pass-1', 'password_set_at' => now()]);

        $this->post(route('login.attempt'), ['phone' => '09121234567', 'password' => 'wrong'])->assertSessionHasErrors('phone');
        $this->assertGuest();

        $this->post(route('login.attempt'), ['phone' => '09121234567', 'password' => 'secret-pass-1'])->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_accounts_without_a_password_cannot_use_password_login(): void
    {
        User::factory()->create(['phone' => '09121234567']);

        $this->post(route('login.attempt'), ['phone' => '09121234567', 'password' => 'password'])
            ->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_user_returns_to_the_page_they_were_trying_to_reach(): void
    {
        User::factory()->create(['phone' => '09121234567', 'password' => 'secret-pass-1', 'password_set_at' => now()]);

        $this->get(route('checkout.index'))->assertRedirect(route('login'));
        $this->post(route('login.attempt'), ['phone' => '09121234567', 'password' => 'secret-pass-1'])
            ->assertRedirect(route('checkout.index'));
    }

    public function test_logout_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create(['phone' => '09121234567']))
            ->post(route('logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }
}
