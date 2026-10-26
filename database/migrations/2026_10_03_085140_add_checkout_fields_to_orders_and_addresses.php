<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->string('title')->nullable()->after('user_id');
            $table->string('plaque', 20)->nullable()->after('address');
            $table->string('unit', 20)->nullable()->after('plaque');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->json('shipping_address')->nullable()->after('address_id');
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('shipping_cost', 15, 2)->default(0)->after('subtotal_amount');
            $table->decimal('packaging_cost', 15, 2)->default(0)->after('shipping_cost');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('packaging_cost');
            $table->string('coupon_code')->nullable()->after('discount_amount');
            $table->boolean('is_gift')->default(false)->after('coupon_code');
            $table->text('gift_message')->nullable()->after('is_gift');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->decimal('wage_percent', 5, 2)->default(0)->after('weight_grams');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('wage_percent');
            $table->decimal('total_price', 15, 2)->default(0)->after('quantity');
        });

        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['percent', 'fixed'])->default('percent');
            $table->decimal('value', 15, 2);
            $table->decimal('min_order', 15, 2)->default(0);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['wage_percent', 'tax_percent', 'total_price']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address', 'subtotal_amount', 'shipping_cost', 'packaging_cost', 'discount_amount', 'coupon_code', 'is_gift', 'gift_message']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn(['title', 'plaque', 'unit']);
        });
    }
};
