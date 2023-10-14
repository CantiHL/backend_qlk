<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paid extends Model
{
    use HasFactory;
    protected $table = 'paids';
    protected $fillable =[
        'sales_id',
        'money',
        'date'
    ];
}
