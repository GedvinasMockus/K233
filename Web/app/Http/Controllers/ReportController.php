<?php

namespace App\Http\Controllers;

use App\Jobs\SendReportInfoJob;
use App\Mail\SendReportInfo;
use App\Models\ParkingLot;
use App\Models\ReportPhoto;
use App\Models\ReportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function uploadReport(Request $request)
    {
        $rules = [
            'description' => ['required', 'string', 'min:3', 'max:1000'],
            'email' => ['required', 'exists:user,email'],
            'parking_lot' => ['required', 'exists:parking_lot,id'],
            'image' => ['required', 'image'],
        ];
        $customMessages = [
            'required' => 'Laukelis turi būti užpildytas!',
            'description.string' => 'Blogas aprašymas!',
            'description.min' => 'Aprašymas per trumpas!',
            'description.max' => 'Aprašymas per ilgas!',
            'email.exists' => 'Vartotojas nerastas!',
            'parking_lot.exists' => 'Pasirinkta aikštelė sistemoje nerasta!',
            'image.image' => 'Netinkamas failo formatas',

        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $data = $request->input();
            $user = User::where('email', '=', $data['email'])->firstOrFail();
            $report = new ReportTicket;
            $report->date = date('Y-m-d H:i:s', time());
            $report->text = $data['description'];
            $report->fk_Userid = $user->id;
            $report->fk_Parking_lotid = $data['parking_lot'];
            $report->save();
            if (!is_null($request->file('image'))) {
                $images = $request->file('image');
                $location = $images->store('reportImages/' . $report->id, 'public');
                $image = new ReportPhoto;
                $image->photo_path = $location;
                $image->fk_Report_ticketid = $report->id;
                $image->save();
            }
            $parkingLot = ParkingLot::findOrFail($data['parking_lot']);
            $details['email'] = $data['email'];
            $details['time'] = $report->date;
            $details['description'] = $data['description'];
            $details['name'] = $parkingLot->parking_name;
            $details['address'] = $parkingLot->city . ', ' . $parkingLot->street . ' ' . $parkingLot->street_number;
            $details['image'] = $image->photo_path;
            dispatch(new SendReportInfoJob($details));
            return response()->json(['status' => 1, 'message' => 'Pažeidimas praneštas sėkmingai!']);
        }
    }
}
