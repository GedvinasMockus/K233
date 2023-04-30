<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    use HasFactory;

    protected $table = 'reservation';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $fillable = [
        'date_from',
        'date_until',
        'full_price',
        'is_inside',
        'fk_Parking_spaceid',
        'fk_Userid'
    ];

    public static function getSpaceAppointments($id)
    {
        $now = Carbon::now();
        $weekStartDate = $now->startOfWeek()->format('Y-m-d H:i');
        $weekEndDate = $now->endOfWeek()->format('Y-m-d H:i');

        return DB::table('reservation')->where('fk_Parking_spaceid', '=', $id)->get();
    }
}
