<?php

namespace App\Models;

use Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An additional weight a product can be ordered in, priced with the product's wage, tax and fee.
 */
class ProductVariant extends Model
{
    /** @use HasFactory<ProductVariantFactory> */
    use HasFactory;

    protected $fillable = ['product_id', 'weight_grams', 'stock', 'sort_order'];

    protected $casts = [
        'weight_grams' => 'decimal:3',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function calculatePrice(?float $goldPricePerGram = null): float
    {
        return $this->product->calculatePrice($goldPricePerGram, (float) $this->weight_grams);
    }
}
