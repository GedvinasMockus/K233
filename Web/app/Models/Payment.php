<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payment';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'date',
        'price',
        'status',
        'fk_Userid'
    ];

    public $timestamps = false;
}
