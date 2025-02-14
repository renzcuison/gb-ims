<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransactionDetails extends Model
{
    protected $fillable = ['stock_transaction_id', 'stock_id', 'sku', 'quantity', 'price_per_unit'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function transaction()
    {
        return $this->belongsTo(StockTransaction::class, 'stock_transaction_id');
    }
}
