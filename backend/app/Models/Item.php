<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
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
    public $incrementing = false;

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
