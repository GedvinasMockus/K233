<?php

namespace App\Http\Controllers;

use App\Jobs\SendReportAnswerJob;
use App\Models\ParkingLot;
use App\Models\ReportPhoto;
use App\Models\ReportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function DisplayDataReport()
    {
        return view('Admin.DataReport');
    }

    public function GenerateDataReport(Request $request)
    {
        $rules = [
            'from' => ['required', 'date'],
            'to' => ['required', 'date', 'after_or_equal:from'],
        ];
        $customMessages = [
            'required' => 'Privaloma pasirinkti rezervuojamą laiką!',
            'date' => 'Blogas rezervuojamo laiko formatas!',
            'after_or_equal' => 'Pasirinkta NUO data negali būti vėlėsnė už pasirinktą IKI datą!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $query = DB::table('reservation')->where('date_from', '>', $request->input('from'))->where('date_until', '<', $request->input('to'));
            $count = $query->count();
            $reservations = $query->select('id', 'date_from', 'date_until', 'full_price')->get();
            $sum = $reservations->sum('full_price');

            return response()->json(['status' => 1, 'count' => $count, 'reservations' => $reservations, 'sum' => $sum]);
        }
    }
    public function DisplayReports()
    {
        $tickets = ReportTicket::getReports(10);
        return view('Admin.Reports')->with(['tickets' => $tickets]);
    }
    public function AnswerToReport(Request $request)
    {
        $data = $request->input();

        $rules = [
            'answer' => ['required', 'min:10'],
            'id_report' => ['required', 'exists:report_ticket,id']
        ];
        $customMessages = [
            'required' => 'Atsakymo laukelis yra privalomas!',
            'min' => 'Per trumpas atsakymas į pažeidimą!',
            'exists' => 'Pažeidimas nerastas sistemoje!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $user = Auth::user();
            $reportInfo = ReportTicket::findOrFail($data['id_report']);
            $userInfo = User::findOrFail($reportInfo->fk_Userid);
            $parkingLotInfo = ParkingLot::findOrFail($reportInfo->fk_Parking_lotid);
            $imageInfo = ReportPhoto::where('fk_Report_ticketid', '=', $reportInfo->id)->firstOrFail();
            $details['email'] = $userInfo->email;
            $details['time'] = $reportInfo->date;
            $details['description'] = $reportInfo->text;
            $details['name'] = $parkingLotInfo->parking_name;
            $details['address'] = $parkingLotInfo->city . ', ' . $parkingLotInfo->street . ' ' . $parkingLotInfo->street_number;
            $details['image'] = $imageInfo->photo_path;
            $details['answer'] = $data['answer'];
            $details['admin'] = $user->name . " " . $user->surname;
            $details['adminEmail'] = $user->email;
            $reportInfo->answered = 1;
            $reportInfo->save();
            dispatch(new SendReportAnswerJob($details));
            return response()->json(['status' => 1]);
        }
    }
}
