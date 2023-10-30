<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Target extends Model
{
    use HasFactory;
    protected $fillable = [
        'staff_id',
        'group_product_id',
        'target',
        'from_date',
        'to_date',
    ];
}
