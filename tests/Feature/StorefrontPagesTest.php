<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render(): void
    {
        $category = Category::create(['name' => 'گوشواره', 'slug' => 'earring', 'is_active' => true, 'sort_order' => 1]);
        Product::create(['category_id' => $category->id, 'name' => 'گوشواره پروانه', 'slug' => 'butterfly', 'is_fixed_price' => true, 'fixed_price' => 1_000_000, 'stock' => 2, 'is_active' => true]);

        foreach (['home', 'products.index', 'about', 'contact', 'login', 'register', 'cart.index', 'cart.drawer'] as $route) {
            $this->get(route($route))->assertOk();
        }

        $this->get(route('home'))->assertSeeText('جدیدترین محصولات')->assertSeeText('گوشواره پروانه');
        $this->get(route('products.index', ['sort' => 'newest']))->assertOk()->assertSeeText('جدیدترین‌ها');
    }

    public function test_footer_uses_the_values_saved_in_site_settings(): void
    {
        Setting::set('site_description', 'توضیح تست فروشگاه');
        Setting::set('footer_copyright', 'کپی‌رایت تست');
        Setting::set('instagram_url', 'https://instagram.com/cattalya');
        Setting::set('whatsapp_number', '09121234567');
        Setting::set('contact_email', 'hello@example.com');

        $this->get(route('home'))
            ->assertSeeText('توضیح تست فروشگاه')
            ->assertSeeText('کپی‌رایت تست')
            ->assertSee('https://instagram.com/cattalya', false)
            ->assertSee('https://wa.me/989121234567', false)
            ->assertSee('mailto:hello@example.com', false);
    }

    public function test_account_pages_require_login_and_render_for_members(): void
    {
        foreach (['profile.show', 'wishlist.index', 'orders.index', 'checkout.index', 'checkout.payment'] as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }

        $user = User::factory()->create(['phone' => '09121234567']);

        foreach (['profile.show', 'wishlist.index', 'orders.index'] as $route) {
            $this->actingAs($user)->get(route($route))->assertOk();
        }
    }

    public function test_guest_visiting_login_page_while_signed_in_is_sent_home(): void
    {
        $this->actingAs(User::factory()->create(['phone' => '09121234567']))
            ->get(route('login'))
            ->assertRedirect();
    }
}
