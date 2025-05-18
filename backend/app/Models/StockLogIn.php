<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLogIn extends Model
{
    use HasFactory;

    protected $table = 'stock_log-in'; 

    protected $fillable = [
        'action',
        'user_name',
        'stock_id',
        'sku',
        'description',
        'qty',
        'reason',
        'supplier',
        'cost_price',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'stock_id', 'id');
    }

    public function sku()
    {
        return $this->belongsTo(Sku::class, 'sku', 'sku');
    }
}
