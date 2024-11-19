<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction_Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_transaction_id', 
        'item_id',
        'quantity',
    ];

    public function inventoryTransaction()
    {
        return $this->belongsTo(Inventory_Transaction::class, 'inventory_transaction_id');
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
