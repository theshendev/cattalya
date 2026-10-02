<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'address_id', 'status', 'total_amount',
        'gold_price_snapshot', 'tracking_code', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'gold_price_snapshot' => 'decimal:2',
    ];

    public const STATUSES = [
        'pending' => 'در انتظار پرداخت',
        'paid' => 'پرداخت شده',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'delivered' => 'تحویل داده شده',
        'cancelled' => 'لغو شده',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
