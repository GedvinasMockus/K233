<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function DisplayParkingLots()
    {
        return view('Reservation.Parking_Lots');
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
