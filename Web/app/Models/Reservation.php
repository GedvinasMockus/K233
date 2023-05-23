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
        'is_admin_created',
        'fk_Parking_spaceid',
        'fk_Userid'
    ];

    public static function getSpaceAppointments($id)
    {
        $startOfWeek = Carbon::now()->startOfWeek()->subWeek()->toDateTimeString();

        $reservations = DB::table('reservation')
            ->where('fk_Parking_spaceid', '=', $id)
            ->whereBetween('date_from', [$startOfWeek, '9999-12-31 23:59:59'])
            ->get();
        return $reservations;
    }
    public static function getReservations($id)
    {
        $now = Carbon::now();
        $nowDate = $now->format('Y-m-d H:i');

        return DB::table('reservation')
            ->join('parking_space', 'reservation.fk_Parking_spaceid', '=', 'parking_space.id')
            ->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')
            ->select([
                'reservation.id', 'reservation.date_from', 'reservation.date_until', 'reservation.full_price', 'parking_space.space_number', 'parking_lot.parking_name',
                DB::raw("CONCAT(parking_lot.city, ', ', parking_lot.street, ', ', parking_lot.street_number) AS address")
            ])
            ->where('fk_Userid', '=', $id)->where('date_until', '>=', $nowDate)->get();
    }
    public function parkingSpace()
    {
        return $this->belongsTo(ParkingSpace::class, 'fk_Parking_spaceid');
    }

    public static function getHistory($id)
    {
        $currentdate = Carbon::now()->toDateTimeString();
        $data = DB::table('reservation')
            ->join('parking_space', 'reservation.fk_Parking_spaceid', '=', 'parking_space.id')
            ->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')
            ->select([
                'reservation.id', 'reservation.date_from', 'reservation.date_until', 'reservation.full_price', 'parking_space.space_number', 'parking_lot.parking_name',
                DB::raw("CONCAT(parking_lot.city, ', ', parking_lot.street, ', ', parking_lot.street_number) AS address")
            ])
            ->where('fk_Userid', $id)
            ->where('date_until', '<=', $currentdate)->get();
        return $data;
    }
    public $timestamps = false;
}
