<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales_item extends Model
{
    use HasFactory;
    protected $table = 'sales_items';
    protected $fillable =[
        'sales_id',
        'product_id',
        'quality',
        'get_more',
        'discount',
        'commission',
        'commission_type',
        'commission_target',
        'price',
        'guarantee'
    ];
    public function Sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
