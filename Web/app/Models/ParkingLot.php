<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLot extends Model
{
    use HasFactory;
    protected $table = 'parking_lot';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'parking_name',
        'photo_path',
        'city',
        'street',
        'street_number',
        'tariff'
    ];
    public $timestamps = false;
}
