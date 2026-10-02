<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_profile(): void
    {
        $this->get(route('profile.show'))->assertRedirect(route('login'));
    }

    public function test_a_user_can_view_and_update_personal_information(): void
    {
        $user = User::factory()->create(['phone' => '09121234567']);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('اطلاعات شخصی');

        $this->actingAs($user)->patch(route('profile.update'), [
            'name' => 'پروانه',
            'last_name' => 'موسوی',
            'phone' => '09121234567',
            'email' => 'parvaneh@example.com',
            'national_id' => '0012345678',
            'birth_date' => '1995-03-12',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'پروانه',
            'last_name' => 'موسوی',
            'national_id' => '0012345678',
        ]);
    }
}
