<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model {
    use HasFactory;

    protected $fillable = [
        'order_code', 'customer_name', 'shipping_address', 'city', 
        'postal_code', 'phone', 'payment_method', 'total_price'
    ];

    public function orders() { 
        return $this->hasMany(Order::class, 'customer_order_id');
    }
}