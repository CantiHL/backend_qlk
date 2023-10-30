<?php

namespace App\Models;

use App\Models\Discount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = [
        'name',
        'code',
        'buy_price',
        'sale_price',
        'color',
        'stock',
        'guarantee',
        'group',
        'active',
    ];
    public function product_groups()
    {
        return $this->belongsTo(Product_Group::class);
    }
    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

}
