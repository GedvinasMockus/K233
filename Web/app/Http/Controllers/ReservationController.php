<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function DisplayParkingLots()
    {
        $lots = DB::table('parking_lot')->select('id', 'parking_name', 'city', 'street', 'street_number')->get();

        return view('Reservation.Parking_Lots', ['lots' => $lots]);
    }

    public function DisplayParkingLot($id)
    {
        return view('Reservation.Parking_Lot', ['id' => $id]);
    }

    public function DisplayReservations()
    {
        return view('Reservation.Reservation_List');
    }

    public function DisplayEditParkingLot($id)
    {
        return view('Reservation.Edit_Parking_Lot', ['id' => $id]);
    }
}
