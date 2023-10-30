<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetPurchase extends Model
{
    use HasFactory;
    protected $fillable = [
        'date',
        'target',
    ];
}
