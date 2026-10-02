<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Payment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_PAID = 'paid';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'order_id', 'gateway', 'amount', 'reference', 'authority',
        'ref_code', 'card_pan', 'status', 'gateway_data', 'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_data' => 'array',
        'paid_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Amount in Rials — what every gateway API actually expects.
     */
    public function amountInRials(): int
    {
        return (int) round($this->amount * config('gateways.toman_to_rial', 10));
    }

    /**
     * Build the merchant-side transaction identifier sent to the gateway.
     * Unique per attempt so a retried order gets a fresh reference.
     */
    public static function generateReference(int $orderId): string
    {
        return 'CTL-'.$orderId.'-'.strtoupper(Str::random(10));
    }

    /**
     * Merge new envelope data into gateway_data without dropping earlier keys.
     */
    public function recordGatewayData(string $key, array $data): void
    {
        $existing = $this->gateway_data ?? [];
        $existing[$key] = $data;

        $this->gateway_data = $existing;
    }
}
