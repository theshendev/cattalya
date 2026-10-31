<?php

namespace App\Services;

use App\Models\GoldPrice;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart';

    public function items(): Collection
    {
        return collect(session(self::KEY, []))->map(fn (array $item, int|string $key) => ['key' => (string) $key] + $item);
    }

    public function add(Product $product, int $quantity = 1, ?ProductVariant $variant = null): void
    {
        $cart = session(self::KEY, []);
        $key = $variant ? $product->id.'-'.$variant->id : (string) $product->id;
        $stockLimit = $product->is_made_to_order ? null : ($variant ?? $product)->stock;

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = $this->limitQuantity($cart[$key]['quantity'] + $quantity, $stockLimit);
        } else {
            $cart[$key] = [
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'weight_grams' => $variant?->weight_grams ?? $product->weight_grams,
                'wage_percent' => $product->wage_percent,
                'tax_percent' => $product->tax_percent,
                'is_fixed_price' => $product->is_fixed_price,
                'fixed_price' => $product->fixed_price,
                'is_made_to_order' => $product->is_made_to_order,
                'stock' => $stockLimit,
                'image' => $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images'),
                'quantity' => $this->limitQuantity($quantity, $stockLimit),
            ];
        }

        session([self::KEY => $cart]);
    }

    public function remove(string $key): void
    {
        $cart = session(self::KEY, []);
        unset($cart[$key]);
        session([self::KEY => $cart]);
    }

    public function update(string $key, int $quantity): void
    {
        $cart = session(self::KEY, []);
        if (isset($cart[$key])) {
            if ($quantity < 1) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $this->limitQuantity($quantity, $cart[$key]['stock']);
            }
        }
        session([self::KEY => $cart]);
    }

    /**
     * Cap a quantity at the available stock; made-to-order items have no cap.
     */
    private function limitQuantity(int $quantity, ?int $stockLimit): int
    {
        return $stockLimit === null ? $quantity : min($quantity, $stockLimit);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return collect(session(self::KEY, []))->sum('quantity');
    }

    public function total(): float
    {
        $goldPricePerGram = GoldPrice::currentPricePerGram();

        return $this->items()->sum(function ($item) use ($goldPricePerGram) {
            $unitPrice = $this->unitPrice($item, $goldPricePerGram);

            return $unitPrice * $item['quantity'];
        });
    }

    public function itemsWithPrices(): Collection
    {
        $goldPricePerGram = GoldPrice::currentPricePerGram();

        return $this->items()->map(function ($item) use ($goldPricePerGram) {
            $item['unit_price'] = $this->unitPrice($item, $goldPricePerGram);
            $item['total_price'] = $item['unit_price'] * $item['quantity'];

            return $item;
        });
    }

    private function unitPrice(array $item, float $goldPricePerGram): float
    {
        if ($item['is_fixed_price']) {
            return (float) $item['fixed_price'];
        }

        $base = $item['weight_grams'] * $goldPricePerGram;
        $wage = $base * ($item['wage_percent'] / 100);
        $tax = ($base + $wage) * ($item['tax_percent'] / 100);

        return $base + $wage + $tax;
    }
}
