<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAndContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_the_message(): void
    {
        $this->post(route('contact.store'), [
            'first_name' => 'پروانه',
            'last_name' => 'موسوی',
            'email' => 'parvaneh@example.com',
            'phone' => '09121234567',
            'description' => 'سلام',
        ])->assertRedirect(route('contact'))->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', ['email' => 'parvaneh@example.com', 'description' => 'سلام']);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->post(route('contact.store'), ['email' => 'nope'])
            ->assertSessionHasErrors(['first_name', 'last_name', 'email', 'phone']);

        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_admin_panel_pages_open_for_admins_only(): void
    {
        ContactMessage::create(['first_name' => 'الف', 'last_name' => 'ب', 'email' => 'a@example.com', 'phone' => '0912']);
        Coupon::factory()->create();

        $admin = User::factory()->create(['phone' => '09121234567', 'is_admin' => true]);
        $customer = User::factory()->create(['phone' => '09127654321']);

        foreach (['products', 'orders', 'coupons', 'contact-messages', 'manage-settings', 'banners', 'categories', 'gold-prices'] as $page) {
            $this->actingAs($admin)->get("/admin/{$page}")->assertOk();
            $this->actingAs($customer)->get("/admin/{$page}")->assertForbidden();
        }
    }

    public function test_admin_can_create_a_made_to_order_product_with_extra_weights(): void
    {
        $category = Category::create(['name' => 'گوشواره', 'slug' => 'earring', 'is_active' => true, 'sort_order' => 1]);
        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]));

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'گوشواره حلقه‌ای',
                'slug' => 'hoop-earring',
                'category_id' => $category->id,
                'weight_grams' => 2.51,
                'stock' => 0,
                'is_made_to_order' => true,
                'variants' => [
                    ['weight_grams' => 3.51, 'stock' => 2],
                    ['weight_grams' => 5.51, 'stock' => 0],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('slug', 'hoop-earring')->firstOrFail();
        $this->assertTrue($product->is_made_to_order);
        $this->assertSame(['3.510', '5.510'], $product->variants->pluck('weight_grams')->all());
    }

    public function test_admin_can_view_an_order_with_its_address_snapshot(): void
    {
        $customer = User::factory()->create(['phone' => '09127654321']);
        $order = Order::create([
            'user_id' => $customer->id,
            'status' => 'paid',
            'total_amount' => 1_000_000,
            'is_gift' => true,
            'gift_message' => 'تولدت مبارک',
            'coupon_code' => 'TS42CK',
            'shipping_address' => ['receiver_name' => 'علی رضایی', 'phone' => '09127654321', 'province' => 'تهران', 'city' => 'تهران', 'address' => 'ولیعصر', 'postal_code' => '1234567890'],
        ]);

        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]))
            ->get("/admin/orders/{$order->id}")
            ->assertOk()
            ->assertSeeText('علی رضایی')
            ->assertSeeText('تولدت مبارک');
    }
}
