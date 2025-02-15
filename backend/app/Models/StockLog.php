<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasFactory;

    protected $table = 'stock_log'; 

    protected $fillable = [
        'stock_id',
        'sku',
        'description',
        'qty',
        'reason',
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
