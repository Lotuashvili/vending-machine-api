<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'seller_id',
        'price',
        'amount',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
