<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $table = 'purchases';
    protected $fillable = [
        'date',
        'status',
        'note',
        'warehouse_id',
    ];
    public function Purchase_item()
    {
        return $this->hasMany(Purchase_item::class);
    }
}
