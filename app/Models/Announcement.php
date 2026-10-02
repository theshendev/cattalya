<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['text', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public static function active(): ?self
    {
        return static::where('is_active', true)->latest()->first();
    }
}
