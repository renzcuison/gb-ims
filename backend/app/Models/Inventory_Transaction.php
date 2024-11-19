<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory_Transaction extends Model
{
    use HasFactory;

    protected $table = 'inventory_transactions'; 

    protected $fillable = [
        'transaction_type',
        'transaction_date',
    ];

    public function items()
    {
        return $this->belongsToMany(Item::class, 'transaction_items')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }
}
