<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = ['customer_order_id', 'stock_id', 'quantity', 'price_per_unit'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function customerOrder()
    {
        return $this->belongsTo(CustomerOrder::class);
    }
}
