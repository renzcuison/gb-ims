<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'item_price_per_unit',
    ];

    public function item()
    {
        return $this->belongsTo(Stock::class);
    }

    public function getItemPricePerUnitAttribute()
    {
        return $this->item ? $this->item->price_per_unit : null;
    }
}