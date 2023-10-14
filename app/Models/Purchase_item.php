<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase_item extends Model
{
    use HasFactory;
    protected $table = 'purchase_items';
    protected $fillable = [
        'purchases_id',
        'product_id',
        'quality',
        'get_more',
        'guarantee',
        'discount',
        'price',
    ];
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
    
}
