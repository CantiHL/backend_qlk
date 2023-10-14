<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
    protected $table = 'sales';
    protected $fillable =[
        'user_id',
        'staff_id',
        'customer_id',
        'warehouse_id',
        'date',
        'status',
        'discount',
        'note',
        'debt',
    ];
    public function Sales_item()
    {
        return $this->hasMany(Sales_item::class);
    }
}
