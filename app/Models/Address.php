<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id', 'title', 'receiver_name', 'phone', 'province',
        'city', 'address', 'plaque', 'unit', 'postal_code', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function fullAddress(): string
    {
        return collect([
            $this->province,
            $this->city,
            $this->address,
            $this->plaque ? 'پلاک '.$this->plaque : null,
            $this->unit ? 'واحد '.$this->unit : null,
        ])->filter()->implode('، ');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
