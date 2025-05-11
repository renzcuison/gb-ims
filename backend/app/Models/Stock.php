<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'item_name',
        'category_id',
        'unit_of_measure',
        'physical_count',
        'on_hand',
        'sold',
        'date',
        'price_per_unit',
        'buying_price',
        'description',
    ];

    public $incrementing = false; 

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function skus()
    {
        return $this->hasMany(Sku::class); 
    }

    public function suppliers()
    {   
        return $this->belongsToMany(Supplier::class, 'stock_supplier', 'stock_id', 'supplier_id');
    }
}
