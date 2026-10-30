<?php

namespace App\Services;

use App\Models\GoldPrice;
use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const KEY = 'cart';

    public function items(): Collection
    {
        return collect(session(self::KEY, []));
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = session(self::KEY, []);
        $id = $product->id;

        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = min($cart[$id]['quantity'] + $quantity, $product->stock);
        } else {
            $cart[$id] = [
                'product_id' => $id,
                'name' => $product->name,
                'slug' => $product->slug,
                'weight_grams' => $product->weight_grams,
                'wage_percent' => $product->wage_percent,
                'tax_percent' => $product->tax_percent,
                'fee' => $product->fee,
                'is_fixed_price' => $product->is_fixed_price,
                'fixed_price' => $product->fixed_price,
                'stock' => $product->stock,
                'image' => $product->getFirstMediaUrl('images', 'thumb') ?: $product->getFirstMediaUrl('images'),
                'quantity' => $quantity,
            ];
        }

        session([self::KEY => $cart]);
    }

    public function remove(int $productId): void
    {
        $cart = session(self::KEY, []);
        unset($cart[$productId]);
        session([self::KEY => $cart]);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = session(self::KEY, []);
        if (isset($cart[$productId])) {
            if ($quantity < 1) {
                unset($cart[$productId]);
            } else {
                $cart[$productId]['quantity'] = min($quantity, $cart[$productId]['stock']);
            }
        }
        session([self::KEY => $cart]);
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

        return Product::calculateGoldPrice(
            (float) $item['weight_grams'],
            (float) $item['wage_percent'],
            (float) $item['tax_percent'],
            (float) ($item['fee'] ?? 0),
            $goldPricePerGram,
        );
    }
}
