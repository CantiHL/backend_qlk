<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_Group extends Model
{
    use HasFactory;
    protected $table = 'product_groups';
    protected $fillable = [
        'group_name',
        'group_code',
        'description',
        'parent',
        'commission',
        'commission_type',
        'commission_target',
    ];
}
