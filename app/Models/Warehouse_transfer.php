<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse_transfer extends Model
{
    use HasFactory;
    protected $table = 'warehouse_transfers';
    protected $fillable = [
        'date_transfer',
        'warehouse_from',
        'warehouse_to',
        'status',
    ];
}
