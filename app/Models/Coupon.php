<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'min_order', 'usage_limit',
        'used_count', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Why the coupon can't be applied to this subtotal, or null when it can.
     */
    public function rejectionReason(float $subtotal): ?string
    {
        return match (true) {
            ! $this->is_active => 'این کد تخفیف فعال نیست.',
            $this->expires_at !== null && $this->expires_at->isPast() => 'این کد تخفیف منقضی شده است.',
            $this->usage_limit !== null && $this->used_count >= $this->usage_limit => 'ظرفیت استفاده از این کد تخفیف تمام شده است.',
            $subtotal < (float) $this->min_order => 'حداقل مبلغ سفارش برای این کد تخفیف '.number_format((float) $this->min_order).' تومان است.',
            default => null,
        };
    }

    public function discountFor(float $subtotal): float
    {
        $discount = $this->type === 'percent'
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $subtotal), 0);
    }
}
