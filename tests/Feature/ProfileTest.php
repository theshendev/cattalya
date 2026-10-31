<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_profile_shows_the_personal_information_cells(): void
    {
        $user = User::factory()->create(['phone' => '09121234567', 'name' => 'پروانه', 'last_name' => 'موسوی']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSeeText('اطلاعات شخصی')
            ->assertSeeText('پروانه موسوی')
            ->assertSeeText('09121234567');
    }

    public function test_identity_can_be_updated_with_a_jalali_birth_date(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->patch(route('profile.identity'), [
            'full_name' => 'پروانه سادات موسوی',
            'national_id' => '0012345678',
            'birth_date' => '1379/01/28',
        ])->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('پروانه', $user->name);
        $this->assertSame('سادات موسوی', $user->last_name);
        $this->assertSame('0012345678', $user->national_id);
        $this->assertSame('2000-04-16', $user->birth_date->toDateString());
    }

    public function test_invalid_identity_values_are_rejected(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->patch(route('profile.identity'), [
            'full_name' => 'علی',
            'national_id' => '123',
            'birth_date' => 'not-a-date',
        ])->assertSessionHasErrors(['national_id', 'birth_date']);
    }

    public function test_password_change_requires_the_current_password_once_one_is_set(): void
    {
        $user = User::factory()->create(['phone' => '09121234567', 'password' => 'old-password-1', 'password_set_at' => now()]);

        $this->actingAs($user)->patch(route('profile.password'), [
            'current_password' => 'wrong',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($user)->patch(route('profile.password'), [
            'current_password' => 'old-password-1',
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
    }

    public function test_a_user_without_a_password_can_set_one(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->patch(route('profile.password'), [
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertSessionHasNoErrors();

        $this->assertTrue($user->fresh()->hasPassword());
    }

    public function test_email_can_be_changed_but_must_be_unique(): void
    {
        User::factory()->create(['phone' => '09127654321', 'email' => 'taken@example.com']);
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->patch(route('profile.email'), ['email' => 'taken@example.com'])->assertSessionHasErrors('email');
        $this->actingAs($user)->patch(route('profile.email'), ['email' => 'new@example.com'])->assertSessionHasNoErrors();

        $this->assertSame('new@example.com', $user->fresh()->email);
    }

    public function test_phone_change_needs_a_confirmation_code_for_the_new_number(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->post(route('profile.phone.otp'), ['phone' => '09125550000'])
            ->assertSessionHas('open_modal', 'phone-verify');
        $this->assertSame('09121234567', $user->fresh()->phone, 'phone is untouched until the code is confirmed');

        $this->actingAs($user)->post(route('profile.phone.verify'), ['otp' => '111111'])->assertSessionHasErrors('otp');
        $this->assertSame('09121234567', $user->fresh()->phone);

        $this->actingAs($user)->post(route('profile.phone.verify'), ['otp' => (string) Cache::get('otp:09125550000')])
            ->assertSessionHasNoErrors();
        $this->assertSame('09125550000', $user->fresh()->phone);
    }

    public function test_phone_verification_offers_a_resend_once_the_cooldown_ends(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);
        $this->actingAs($user)->post(route('profile.phone.otp'), ['phone' => '09125550000']);

        $this->get(route('profile.show', ['details' => 1]))
            ->assertOk()
            ->assertSee('name="phone" value="09125550000"', false)
            ->assertViewHas('phoneResendIn', fn (int $seconds) => $seconds > 0);

        $this->travel(3)->minutes();

        $this->get(route('profile.show', ['details' => 1]))
            ->assertViewHas('phoneResendIn', 0);
    }

    public function test_phone_change_rejects_a_number_that_belongs_to_someone_else(): void
    {
        User::factory()->create(['phone' => '09127654321']);
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->post(route('profile.phone.otp'), ['phone' => '09127654321'])->assertSessionHasErrors('phone');
    }
}
