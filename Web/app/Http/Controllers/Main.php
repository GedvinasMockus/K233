<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Main extends Controller
{
    public function DisplayMain()
    {
        $data = DB::table('test')->orderBy("date", "desc")->get();
        return view('Random')->with(["data" => $data]);
    }

    public function InsertData($data)
    {
        //$data = $request->input();
        DB::insert('insert into test (uuid, date) values (?,?)', [$data, now()]);
    }
    public function Index()
    {
        return view('index');
    }
}
