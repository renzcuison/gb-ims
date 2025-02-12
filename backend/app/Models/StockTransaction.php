<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    protected $fillable = ['transaction_type', 'reason', 'created_by'];

    public function details()
    {
        return $this->hasMany(StockTransactionDetails::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
