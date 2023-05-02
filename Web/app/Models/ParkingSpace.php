<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSpace extends Model
{
    use HasFactory;
    protected $table = 'parking_space';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'space_number',
        'x1',
        'y1',
        'x2',
        'y2',
        'x3',
        'y3',
        'x4',
        'y4',
        'fk_Parking_lotid'
    ];
    public $timestamps = false;
}
