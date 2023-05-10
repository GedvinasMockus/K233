<?php

namespace App\Http\Controllers;

use App\Models\ParkingLot;
use App\Models\ParkingSpace;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\ReservationCheckRule;
use App\Rules\ReservationConflictRule;
use App\Rules\StartDateRule;
use App\Rules\SufficientBalanceRule;
use App\Rules\ValidHoursRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
    public function DisplayParkingLots()
    {
        $lots = DB::table('parking_lot')->select('id', 'parking_name', 'city', 'street', 'street_number')->get();

        return view('Reservation.Parking_Lots', ['lots' => $lots]);
    }

    public function DisplayParkingLot($id)
    {
        $spaces = DB::table('parking_space')->where('fk_Parking_lotid', $id)->get();
        $photo = DB::table('parking_lot')->select('photo_path')->where('id', $id)->first()->photo_path;

        // dd($photo);
        // dd($spaces);

        return view('Reservation.Parking_Lot', compact('id', 'spaces', 'photo'));
    }

    public function DisplayParkingSpace($id)
    {
        $lot = DB::table('parking_space')
            ->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')->select('parking_lot.*')->where('parking_space.id', '=', $id)->first();

        $space = DB::table('parking_space')->where('id', $id)->first();
        $reservations = Reservation::getSpaceAppointments($space->id);

        $events = [];

        foreach ($reservations as $reservation) {
            $events[] = [
                'title' => $reservation->fk_Userid == @auth()->user()->id ? "Jūsų rezervacija" : "Rezervacija",
                'start' => $reservation->date_from,
                'end' => $reservation->date_until,
                'backgroundColor' =>  $reservation->fk_Userid == @auth()->user()->id ? "darkGreen" : "red",
            ];
        }
        $events = json_encode($events);
        return view('Reservation.Parking_Space', compact('id', 'lot', 'space', 'events'));
    }

    public function MakeReservation(Request $request)
    {
        $rules = [
            'startDate' => ['required', 'date', new StartDateRule],
            'endDate' => ['required', 'date'],
            'id' => ['required', 'exists:parking_space,id', new ReservationConflictRule($request->startDate, $request->endDate, $request->id), new SufficientBalanceRule],
            'hours' => ['required', new ValidHoursRule],
        ];
        $customMessages = [
            'required' => 'Privaloma pasirinkti rezervuojamą laiką!',
            'date' => 'Blogas rezervuojamo laiko formatas!',
            'exists' => 'Rezervuojama vieta sistemoje neegzistuoja!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $data = $request->input();
            $user = Auth::user();
            $space = ParkingSpace::findOrFail($data["id"]);
            $lot = ParkingLot::findOrFail($space->fk_Parking_lotid);
            $tariff = $lot->tariff;
            $requiredCost = $data["hours"] * $tariff;
            $reservation = new Reservation;
            $reservation->date_from = $data['startDate'];
            $reservation->date_until = $data['endDate'];
            $reservation->full_price = $requiredCost;
            $reservation->is_inside = 0;
            $reservation->fk_Parking_spaceid = $data['id'];
            $reservation->fk_Userid = $user->id;
            $reservation->is_admin_created = 0;
            $reservation->save();
            $updateUser = User::find($user->id);
            $updateUser->balance = $user->balance - $requiredCost;
            $updateUser->save();

            $reservations = Reservation::getSpaceAppointments($data['id']);

            $events = [];

            foreach ($reservations as $reservation) {
                $events[] = [
                    'title' => $reservation->fk_Userid == $user->id ? "Jūsų rezervacija" : "Rezervacija",
                    'start' => $reservation->date_from,
                    'end' => $reservation->date_until,
                    'backgroundColor' =>  $reservation->fk_Userid == $user->id ? "darkGreen" : "red",
                ];
            }
            $events = json_encode($events);
            return response()->json(['status' => 1, 'events' => $events]);
        }
    }

    public function DisplayReservations()
    {
        $user = Auth::user();
        $reservations = Reservation::getReservations($user->id);

        $events = [];
        foreach ($reservations as $reservation) {
            $description = [
                'parking_name' => $reservation->parking_name,
                'space_number' => $reservation->space_number,
                'address' => $reservation->address,
                'id' => $reservation->id,
                'price' => $reservation->full_price
            ];
            $events[] = [
                'start' => $reservation->date_from,
                'end' => $reservation->date_until,
                'backgroundColor' =>  "darkGreen",
                'title' => "Rezervacija",
                'extendedProps' => $description,
            ];
        }
        $events = json_encode($events);
        return view('Reservation.Reservation_List')->with(['events' => $events]);
    }

    public function RemoveReservation(Request $request)
    {
        $data = $request->input();
        $rules = [
            'id' => ['required', 'exists:reservation,id', new ReservationCheckRule],
        ];
        $customMessages = [
            'required' => 'Privaloma pasirinkti naikinamą rezervuojamą laiką!',
            'exists' => 'Rezervuojama vieta sistemoje neegzistuoja!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $user = Auth::user();
            $reservationRem = Reservation::findOrFail($data["id"]);
            if (!$reservationRem->is_admin_created) {
                $updateUser = User::find($user->id);
                $updateUser->balance = $user->balance + $reservationRem->full_price;
                $updateUser->save();
            }
            Reservation::find($data["id"])->delete();
            $reservations = Reservation::getReservations($user->id);
            $events = [];
            foreach ($reservations as $reservation) {
                $description = [
                    'parking_name' => $reservation->parking_name,
                    'space_number' => $reservation->space_number,
                    'address' => $reservation->address,
                    'id' => $reservation->id,
                    'price' => $reservation->full_price
                ];
                $events[] = [
                    'start' => $reservation->date_from,
                    'end' => $reservation->date_until,
                    'backgroundColor' =>  "darkGreen",
                    'title' => "Rezervacija",
                    'extendedProps' => $description,
                ];
            }
            $events = json_encode($events);
            return response()->json(['status' => 1, 'events' => $events]);
        }
    }

    public function UserReservation($id)
    {
        $lot = DB::table('parking_space')
            ->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')->select('parking_lot.*')->where('parking_space.id', '=', $id)->first();

        $space = DB::table('parking_space')->where('id', $id)->first();
        $reservations = Reservation::getSpaceAppointments($space->id);

        $events = [];

        foreach ($reservations as $reservation) {
            $userData = User::find($reservation->fk_Userid);
            $description = [
                'name' => $userData->name,
                'surname' => $userData->surname,
                'email' => $userData->email,
                'isNewEvent' => true,
            ];
            $events[] = [
                'title' => "Rezervacija",
                'start' => $reservation->date_from,
                'end' => $reservation->date_until,
                'backgroundColor' =>  "red",
                'extendedProps' => $description,
            ];
        }
        $events = json_encode($events);
        return view('Reservation.Parking_Space_Admin', compact('id', 'lot', 'space', 'events'));
    }

    public function MakeUserReservation(Request $request)
    {
        $data = $request->input();
        Log::info(print_r($data, true));
        $rules = [
            'start' => ['required'],
            'end' => ['required'],
            'start[]' => ['date'],
            'end[]' => ['date'],
            'id' => ['required', 'exists:parking_space,id'],
            'user' => ['required', 'exists:user,id'],
        ];
        $customMessages = [
            'required' => 'Privaloma pasirinkti rezervuojamą laiką!',
            'date' => 'Blogas rezervuojamo laiko formatas!',
            'id.exists' => 'Rezervuojama vieta sistemoje neegzistuoja!',
            'user.required' => 'Prašome pasirinkti darbuotoją!',
            'user.exists' => 'Darbuotojas neegzistuoja sistemoje!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            for ($i = 0; $i < sizeof($data["start"]); $i++) {
                $reservation = new Reservation;
                Log::info(print_r($data['start'][$i], true));
                $reservation->date_from = $data['start'][$i];
                $reservation->date_until = $data['end'][$i];
                $reservation->full_price = 0;
                $reservation->is_inside = 0;
                $reservation->fk_Parking_spaceid = $data['id'];
                $reservation->fk_Userid = $data['user'];
                $reservation->is_admin_created = 1;
                $reservation->save();
            }

            $reservations = Reservation::getSpaceAppointments($data['id']);

            $events = [];

            foreach ($reservations as $reservation) {
                $userData = User::find($reservation->fk_Userid);
                $description = [
                    'name' => $userData->name,
                    'surname' => $userData->surname,
                    'email' => $userData->email,
                    'isNewEvent' => true,
                ];
                $events[] = [
                    'title' => "Rezervacija",
                    'start' => $reservation->date_from,
                    'end' => $reservation->date_until,
                    'backgroundColor' =>  "red",
                    'extendedProps' => $description,
                ];
            }
            $events = json_encode($events);
            return response()->json(['status' => 1, 'events' => $events]);
        }
    }

    public function DisplayEditParkingLot($id)
    {
        return view('Reservation.Edit_Parking_Lot', ['id' => $id]);
    }

    public function DisplayNewParkingLot()
    {

        return view('Reservation.Parking_Lot_Add');
    }

    public function SaveLots(Request $request)
    {
        Log::info($request);
        $newspaces = $request->input('points');
        // Log::info($newlots);

        $lot = DB::table('parking_lot')->insertGetId([
            'parking_name' => $request->input('name'),
            'photo_path' => $request->input('path'),
            'city' => $request->input('city'),
            'street' => $request->input('street'),
            'street_number' => $request->input('number'),
            'tariff' => $request->input('tariff')
        ]);

        Log::info($lot);

        $lotNr = 0;
        foreach ($newspaces as $space) {
            $arrayNr = 0;
            $points = explode(" ", $space);
            $allpoints = array();
            // Log::info($points);
            foreach ($points as $point) {
                $point = explode(",", $point);
                $allpoints[$arrayNr] = $point;
                // array_push($allpoints, $point);
                $arrayNr++;
            }
            // Log::info($allpoints);

            $lotNr++;

            DB::table('parking_space')->insert([
                'space_number' => $lotNr,
                'x1' => $allpoints[0][0],
                'y1' => $allpoints[0][1],
                'x2' => $allpoints[1][0],
                'y2' => $allpoints[1][1],
                'x3' => $allpoints[2][0],
                'y3' => $allpoints[2][1],
                'x4' => $allpoints[3][0],
                'y4' => $allpoints[3][1],
                'fk_Parking_lotid' => $lot
            ]);
        }



        return response()->json(['success' => 'Got Simple Ajax Request.']);
    }

    public function Test()
    {
        return view('Reservation.Test');
    }
}
