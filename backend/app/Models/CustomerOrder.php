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

    // Define relationship with OrderItem
    public function items() {
        return $this->hasMany(Order::class, 'order_id');
    }
}
