<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $table = 'warehouses';
    protected $fillable = [
        'fullname',
        'phone',
        'address',
        'top_invoice',
        'middle_invoice',
        'bottom_invoice',
        'note_invoice',
    ];
}
