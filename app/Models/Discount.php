<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;
    protected $table = 'discounts';
    protected $fillable = [
        'product_id',
        'from_date',
        'to_date',
        'discount',
        'get_more',
        'inv_condition',
    ];
    public function products()
    {
        return $this->belongsTo(Products::class, 'product_id');
    }
}
