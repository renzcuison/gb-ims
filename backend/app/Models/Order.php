<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'quantity',
        'price_per_unit',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id');
    }

}