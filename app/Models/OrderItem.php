<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'product_variant_id', 'product_name',
        'weight_grams', 'wage_percent', 'tax_percent', 'unit_price', 'quantity', 'total_price',
    ];

    protected $casts = [
        'weight_grams' => 'decimal:3',
        'wage_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function subtotal(): float
    {
        return (float) $this->unit_price * $this->quantity;
    }
}
