<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoldPrice extends Model
{
    protected $fillable = ['price_per_gram', 'source', 'fetched_at'];

    protected $casts = [
        'price_per_gram' => 'decimal:2',
        'fetched_at' => 'datetime',
    ];

    public static function current(): ?self
    {
        return static::latest()->first();
    }

    public static function currentPricePerGram(): float
    {
        return (float) (static::current()?->price_per_gram ?? 0);
    }
}
