<?php

namespace App\Http\Controllers;

use App\Models\ParkingLot;
use App\Models\ParkingSpace;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\ReservationCheckRule;
use App\Rules\ReservationCombineRule;
use App\Rules\ReservationConflictAdminRule;
use App\Rules\ReservationConflictRule;
use App\Rules\ReservationUpdateConflictRule;
use App\Rules\StartDateAdminRule;
use App\Rules\StartDateRule;
use App\Rules\SufficientBalanceRule;
use App\Rules\ValidHoursRule;
use Carbon\Carbon;
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
        $data = $request->input();
        $rules = [
            'startDate' => ['required', 'date', new StartDateRule],
            'endDate' => ['required', 'date'],
            'id' => ['required', 'exists:parking_space,id', new ReservationConflictRule($request->startDate, $request->endDate, $request->id), new SufficientBalanceRule()],
            'hours' => ['required', new ValidHoursRule],
            'oldData' => [new ReservationCombineRule($request->id)],
            'newest' => ['date'],
            'oldest' => ['date']
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
            $space = ParkingSpace::findOrFail($data["id"]);
            $lot = ParkingLot::findOrFail($space->fk_Parking_lotid);
            $tariff = $lot->tariff;
            $requiredCost = $data["hours"] * $tariff;
            $user = Auth::user();
            $oldest = $data['startDate'];
            $newest = $data['endDate'];
            $isInside = 0;
            if (!empty($data['oldData'])) {
                $oldest = $data['oldest'];
                $newest = $data['newest'];
                $oldData = json_decode($data['oldData'], true);
                for ($i = 0; $i < sizeof($oldData); $i++) {
                    $oldRezervation = Reservation::where([
                        ['date_from', '=', $oldData[$i]['start']],
                        ['date_until', '=', $oldData[$i]['end']],
                        ['fk_Userid', '=', $user->id],
                        ['fk_Parking_spaceid', '=', $data['id']],
                    ])->firstOrFail();
                    if ($oldRezervation->is_inside == 1) {
                        $isInside = 1;
                    }
                    $requiredCost += $oldRezervation->full_price;
                    $oldRezervation->delete();
                }
            }
            $reservation = new Reservation;
            $reservation->date_from = $oldest;
            $reservation->date_until = $newest;
            $reservation->full_price = $requiredCost;
            $reservation->is_inside = $isInside;
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

    public function DisplayReservations(Request $request)
    {
        $user = Auth::user();
        $reservations = Reservation::getReservations($user->id);
        $success = $request->query('success');
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

        if ($success) {
            return redirect()->route('DisplayReservations')->with('success', 'Rezervacija atnaujinta sėkmingai!');
        } else {
            return view('Reservation.Reservation_List')->with(['events' => $events, 'success' => $request->session()->get('success')]);
        }
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
            $updateUser = User::find($user->id);
            $updateUser->balance = $user->balance + $reservationRem->full_price;
            $updateUser->save();
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

    public function EditReservation($id)
    {
        $lot = DB::table('reservation')
            ->join('parking_space', 'reservation.fk_Parking_spaceid', '=', 'parking_space.id')
            ->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')
            ->select(
                'parking_lot.*',
                'reservation.*',
                'parking_space.space_number',
                DB::raw('FORMAT(TIMESTAMPDIFF(MINUTE, reservation.date_from, reservation.date_until) / 60, IF(FLOOR(TIMESTAMPDIFF(MINUTE, reservation.date_from, reservation.date_until) / 60) = (TIMESTAMPDIFF(MINUTE, reservation.date_from, reservation.date_until) / 60), 0, 1)) AS hour_amount')
            )
            ->where('reservation.id', '=', $id)
            ->first();


        $space = Reservation::findOrFail($lot->fk_Parking_spaceid);
        $reservations = Reservation::getSpaceAppointments($space->id);
        $user = Auth::user();
        $events = [];

        foreach ($reservations as $reservation) {
            $description = [
                'isUserEvent' => $reservation->fk_Userid == $user->id ? true : false,
            ];
            $events[] = [
                'title' => $reservation->fk_Userid == $user->id ? "Jūsų rezervacija" : "Rezervacija",
                'start' => $reservation->date_from,
                'end' => $reservation->date_until,
                'backgroundColor' =>  $reservation->id == $id ? "darkGreen" : "grey",
                'extendedProps' => $description,
            ];
        }
        $events = json_encode($events);
        return view('Reservation.Reservation_Edit')->with(['id' => $id, 'lot' => $lot, 'space' => $space, 'events' => $events]);
    }

    public function UpdateReservation(Request $request)
    {
        $data = $request->input();
        $rules = [
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', new StartDateRule],
            'id' => ['required', 'exists:reservation,id', new ReservationUpdateConflictRule($request->startDate, $request->endDate, $request->id), new SufficientBalanceRule($request->id)],
            'hours' => ['required', new ValidHoursRule],
            'oldData' => [new ReservationCombineRule((Reservation::findOrFail($request->id)->fk_Parking_spaceid))],
            'newest' => ['date'],
            'oldest' => ['date']
        ];
        $customMessages = [
            'required' => 'Privaloma pasirinkti rezervuojamą laiką!',
            'date' => 'Blogas rezervuojamo laiko formatas!',
            'exists' => 'Redaguojama reservacija nerasta!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $reservation = Reservation::find($data["id"]);
            $space = ParkingSpace::findOrFail($reservation->fk_Parking_spaceid);
            $lot = ParkingLot::findOrFail($space->fk_Parking_lotid);
            $start = Carbon::parse($reservation->date_from);
            $end = Carbon::parse($reservation->date_until);
            $minutesDifference = $end->diffInMinutes($start);
            $hoursDifference = $minutesDifference / 60;
            $tariff = $lot->tariff;
            $fullPrice = ($tariff * ($data["hours"] - $hoursDifference));
            if ($reservation->full_price  <= -$fullPrice) {
                $fullPrice = $reservation->full_price * -1;
            }
            $reservationPrice = $reservation->full_price + $fullPrice;
            if ($reservationPrice <= 0) {
                $reservationPrice = 0;
            }

            $user = Auth::user();
            $oldest = $data['startDate'];
            $newest = $data['endDate'];
            $isInside = 0;
            if (!empty($data['oldData'])) {
                $oldest = $data['oldest'];
                $newest = $data['newest'];
                $oldData = json_decode($data['oldData'], true);
                for ($i = 0; $i < sizeof($oldData); $i++) {
                    $oldRezervation = Reservation::where([
                        ['date_from', '=', $oldData[$i]['start']],
                        ['date_until', '=', $oldData[$i]['end']],
                        ['fk_Userid', '=', $user->id],
                        ['fk_Parking_spaceid', '=', $reservation->fk_Parking_spaceid],
                    ])->firstOrFail();
                    if ($oldRezervation->is_inside == 1) {
                        $isInside = 1;
                    }
                    $reservationPrice  += $oldRezervation->full_price;
                    $oldRezervation->delete();
                }
            }
            $reservation->date_from = $oldest;
            $reservation->date_until = $newest;
            $reservation->full_price = $reservationPrice;
            $reservation->is_inside = $isInside;
            $reservation->is_admin_created = 0;
            $reservation->save();
            $updateUser = User::find($user->id);
            $updateUser->balance = $user->balance - $fullPrice;
            $updateUser->save();
            return response()->json(['status' => 1]);
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
        $rules = [
            'start' => ['required', new ReservationConflictAdminRule($data['start'], $data['end'], $data['id'], $data['user']), new StartDateAdminRule($data['start'])],
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
                $combineReserBefore = Reservation::where([
                    ['fk_Userid', '=', $data['user']],
                    ['fk_Parking_spaceid', '=', $data['id']],
                    ['date_until', '=', $data['start'][$i]]
                ])->first();
                $combineReserAfter = Reservation::where([
                    ['fk_Userid', '=', $data['user']],
                    ['fk_Parking_spaceid', '=', $data['id']],
                    ['date_from', '=', $data['end'][$i]]
                ])->first();
                $price = 0;
                $isInside = 0;
                $start = $data['start'][$i];
                $end = $data['end'][$i];
                if (!empty($combineReserBefore)) {
                    $price += $combineReserBefore->full_price;
                    if ($combineReserBefore->is_inside == 1) {
                        $isInside = 1;
                    }
                    $start = $combineReserBefore->date_from;
                    $combineReserBefore->delete();
                }
                if (!empty($combineReserAfter)) {
                    $price += $combineReserAfter->full_price;
                    if ($combineReserAfter->is_inside == 1) {
                        $isInside = 1;
                    }
                    $end = $combineReserAfter->date_until;
                    $combineReserAfter->delete();
                }
                $reservation = new Reservation;
                $reservation->date_from = $start;
                $reservation->date_until = $end;
                $reservation->full_price = $price;
                $reservation->is_inside = $isInside;
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
