<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shipping_address',
        'city',
        'postal_code',
        'phone',
        'payment_method',
        'status',
        'total_price',
        'customer_name',
        'order_code',
        'customer_order_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_order_id');
    }
}
