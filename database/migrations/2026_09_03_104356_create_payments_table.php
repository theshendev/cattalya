<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Kept as a plain string: adding a driver must not require an
            // enum migration, and SQLite cannot alter enums in place.
            $table->string('gateway', 32);

            // Amount in Tomans, mirroring orders.total_amount.
            $table->decimal('amount', 15, 2);

            // Our own identifier, generated before the gateway is called. It
            // is what we send as ZarinPal metadata.order_id / IDPay order_id /
            // DigiPay providerId, and it is how a callback is matched back to
            // this row. Unique so a replayed callback cannot hit two payments.
            $table->string('reference', 64)->unique();

            // The gateway's own handle for the transaction:
            // ZarinPal authority, IDPay id, DigiPay ticket.
            $table->string('authority')->nullable()->index();

            // Bank reference shown to the customer:
            // ZarinPal ref_id, IDPay track_id, DigiPay rrn.
            $table->string('ref_code')->nullable();

            // Masked PAN returned at verify time, e.g. 502229******7467.
            $table->string('card_pan', 32)->nullable();

            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // Full request/response envelopes for reconciliation and disputes.
            $table->json('gateway_data')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
