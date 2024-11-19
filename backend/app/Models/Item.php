<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'supplier_id',
        'price_per_unit',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventoryTransactions()
    {
        return $this->belongsToMany(Inventory_Transaction::class, 'transaction_items')
                    ->withPivot('quantity', 'unit_price')
                    ->withTimestamps();
    }
}
