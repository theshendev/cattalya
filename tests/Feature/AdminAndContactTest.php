<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Filament\Widgets\LatestOrdersWidget;
use App\Filament\Widgets\SalesChartWidget;
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

    public function test_admin_panel_is_rendered_in_persian_right_to_left(): void
    {
        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertSee('lang="fa"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_admin_dashboard_renders_with_orders_and_navigation_badges(): void
    {
        $customer = User::factory()->create(['phone' => '09127654321']);
        Order::create(['user_id' => $customer->id, 'status' => 'pending', 'total_amount' => 500_000]);
        Order::create(['user_id' => $customer->id, 'status' => 'paid', 'total_amount' => 750_000]);

        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]))
            ->get('/admin')
            ->assertOk()
            ->assertSeeText('فروشگاه')
            ->assertSee('--cat-gold', false);

        Livewire::test(LatestOrdersWidget::class)->assertCanSeeTableRecords(Order::all());
        Livewire::test(SalesChartWidget::class)->assertOk();
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

    public function test_admin_can_filter_orders_by_status_tab_and_bulk_change_status(): void
    {
        $customer = User::factory()->create(['phone' => '09127654321']);
        $paid = Order::create(['user_id' => $customer->id, 'status' => 'paid', 'total_amount' => 750_000]);
        $pending = Order::create(['user_id' => $customer->id, 'status' => 'pending', 'total_amount' => 500_000]);

        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]));

        Livewire::test(ListOrders::class)
            ->set('activeTab', 'paid')
            ->assertCanSeeTableRecords([$paid])
            ->assertCanNotSeeTableRecords([$pending])
            ->callTableAction('markShipped', $paid);

        $this->assertSame('shipped', $paid->fresh()->status);

        Livewire::test(ListOrders::class)
            ->callTableBulkAction('changeStatus', [$pending], ['status' => 'cancelled']);

        $this->assertSame('cancelled', $pending->fresh()->status);
    }

    public function test_admin_product_list_tabs_separate_low_stock_products(): void
    {
        $category = Category::create(['name' => 'گردنبند', 'slug' => 'necklace']);
        $productData = ['category_id' => $category->id, 'weight_grams' => 2];
        $lowStock = Product::create($productData + ['name' => 'کم', 'slug' => 'low', 'stock' => 1]);
        $wellStocked = Product::create($productData + ['name' => 'زیاد', 'slug' => 'high', 'stock' => 10]);

        $this->actingAs(User::factory()->create(['phone' => '09121234567', 'is_admin' => true]));

        Livewire::test(ListProducts::class)
            ->set('activeTab', 'low_stock')
            ->assertCanSeeTableRecords([$lowStock])
            ->assertCanNotSeeTableRecords([$wellStocked]);
    }
}
