<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description',
        'karat', 'gold_color', 'shine', 'dimensions',
        'weight_grams', 'wage_percent', 'tax_percent', 'fee',
        'is_fixed_price', 'fixed_price', 'stock', 'is_made_to_order', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_fixed_price' => 'boolean',
        'is_active' => 'boolean',
        'is_made_to_order' => 'boolean',
        'weight_grams' => 'decimal:3',
        'wage_percent' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'fee' => 'decimal:2',
        'fixed_price' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order')->orderBy('weight_grams');
    }

    /**
     * Active products a customer can order: in stock in some weight, or made to order.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeAvailable(Builder $query): void
    {
        $query->where('is_active', true)->where(fn (Builder $availability) => $availability
            ->where('stock', '>', 0)
            ->orWhere('is_made_to_order', true)
            ->orWhereHas('variants', fn (Builder $variants) => $variants->where('stock', '>', 0)));
    }

    /**
     * Whether the given weight option (the product itself when null) can be added to the cart.
     */
    public function canBeOrdered(?ProductVariant $variant = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return $this->is_made_to_order || ($variant ?? $this)->stock > 0;
    }

    /**
     * Calculate product price based on current gold price.
     * Formula: (weight × gold_rate) + wage% + tax% + fee
     */
    public function calculatePrice(?float $goldPricePerGram = null, ?float $weightGrams = null): float
    {
        if ($this->is_fixed_price) {
            return (float) $this->fixed_price;
        }

        $goldPrice = $goldPricePerGram ?? GoldPrice::currentPricePerGram();
        $base = ($weightGrams ?? (float) $this->weight_grams) * $goldPrice;
        $wage = $base * ((float) $this->wage_percent / 100);
        $tax = ($base + $wage) * ((float) $this->tax_percent / 100);
        $fee = (float) $this->fee;

        return round($base + $wage + $tax + $fee, 0);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->nonQueued();
        $this->addMediaConversion('card')->width(600)->height(600)->nonQueued();
    }
}
