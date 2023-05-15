<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        Log::info($request);

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
            Log::info($reservations);

            $sum = $reservations->sum('full_price');

            return response()->json(['status' => 1, 'count' => $count, 'reservations' => $reservations, 'sum' => $sum]);
        }
    }
}
