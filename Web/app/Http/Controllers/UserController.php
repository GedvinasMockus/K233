<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function DisplayLogin()
    {
        return view('Auth.Login');
    }
    public function Validate_Login(Request $request)
    {
        $rules = [
            'email' => 'required',
            'password' => 'required'
        ];
        $customMessages = [
            'email.required' => 'El. pašto laukelis turi būti užpildytas!',
            'password.required' => 'Slaptažodžio laukelis turi būti užpildytas!',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return redirect('Login')->withInput()->withErrors($validator);
        }
        $user = User::where('email', '=', $request['email'])->first();
        if (@Hash::check($request->get('password'), $user->password)) {
            // if ($user->privilegijos == 1) {
            //     $data = $request->input();
            //     $confirm = Str::random(60);
            //     $request->session()->put('registration', $confirm);
            //     $request->session()->put('email', $data['email']);
            //     $dataSend = [
            //         'confirm' => $confirm,
            //         'email' => $data['email']
            //     ];
            //     // Mail::send('Emails.Register', $dataSend, function ($message) use ($data) {
            //     //     $message->to($data['email'])->subject('Registracijos patvirtinimas');
            //     // });
            //     return redirect('Login')->with('errorNotConfirmed', 'Jūsų paskyra dar nėra patvirtinta!')->withInput();
            // }
            Auth::loginUsingId($user->id);
            return redirect('');
        }
        return redirect('Login')->with('error', 'Prisijungimo duomenys neteisingi!')->withInput();
    }
    public function DisplayRegister()
    {
        return view('Auth.Register');
    }

    public function Validate_Reg(Request $request)
    {
        $rules = [
            'name' => 'required|alpha|min:3|max:20',
            'surname' => 'required|alpha|min:3|max:20',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|min:8|confirmed'
        ];
        $customMessages = [
            'required' => 'Laukelis turi būti užpildytas!',
            'alpha' => 'Laukelyje gali būti tik raidės!',
            'min' => 'Laukelyje esanti informacija per trumpa!',
            'max' => 'Laukelyje esanti informacija per ilga!',
            'unique' => 'Laukelyje esanti informacija jau egzistuoja sistemoje!',
            'confirmed' => 'Slaptažodžiai nesutampa!',
            'email' => 'Blogas el. pašto formatas!',
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return redirect('Register')
                ->withInput()
                ->withErrors($validator);
        } else {
            $data = $request->input();
            //dd($data);
            try {
                // $confirm = Str::random(60);
                // $request->session()->put('registration', $confirm);
                // $request->session()->put('email', $data['email']);
                // $dataSend = [
                //     'confirm' => $confirm,
                //     'email' => $data['email']
                // ];
                // Mail::send('Emails.Register', $dataSend, function ($message) use ($data) {
                //     $message->to($data['email'])->subject('Registracijos patvirtinimas');
                // });
                $user = new User;
                $user->name = $data['name'];
                $user->surname = $data['surname'];
                $user->email = $data['email'];
                $user->phone_number = "jkl";
                $user->password = Hash::make($data['password']);
                $user->uuid = Str::uuid();
                $user->balance = 0.00;
                $user->role = 1;
                $user->save();

                return redirect('Login')->with('success', 'Registracija sėkminga!');
            } catch (Exception $e) {
                return redirect('Register')->with('failed', 'Registracija nesėkminga! Bandykite iš naujo.')->withInput();
            }
        }
    }
    public function Logout()
    {
        Session::flush();
        return Redirect('');
    }

    public function DisplayUserProfile()
    {
        return view('Profile.User_profile');
    }

    public function DisplayProfiles()
    {
        $users = DB::table('user')->select('id', 'name', 'surname', 'email')->get();
        // dd($users);

        return view('Profile.Profiles', ['users' => $users]);
    }

    public function DisplayProfile($id)
    {
        if(DB::table('user')->where('id', $id)->first()->role == 3)
        {
            $isblocked = True;
        }
        else
        {
            $isblocked = False;
        }

        return view('Profile.Profile', ['id' => $id], ['isblocked' => $isblocked]);
    }

    public function BanUser($id)
    {
        $temp = DB::table('user')->where('id', $id)->update(['role' => 3]);

        return redirect()->route('DisplayProfile', ['id' => $id]);
    }

    public function UnbanUser($id)
    {
        $temp = DB::table('user')->where('id', $id)->update(['role' => 1]);

        return redirect()->route('DisplayProfile', ['id' => $id]);
    }

    public function DisplayHistory()
    {
        $id = Auth::user()->id;
        $currentdate = Carbon::now()->toDateTimeString();

        $pastreservations = DB::table('reservation')->join('parking_space', 'reservation.fk_Parking_spaceid', '=', 'parking_space.id')->join('parking_lot', 'parking_space.fk_Parking_lotid', '=', 'parking_lot.id')->select('reservation.id', 'reservation.date_from', 'reservation.date_until', 'parking_lot.parking_name')->where('fk_Userid', $id)->where('date_until', '<=', $currentdate)->get();

        return view('Profile.History', ['pastreservations' => $pastreservations]);
    }

    public function DisplayChangeStatus($id)
    {
        $user = Auth::user();
        $statuses = DB::table('user_role')->where('id_User_role', '!=', 3)->get();

        $statuseslt = ['Nepatvirtintas vartotojas', 'Paprastas vartotojas', 'Administratorius'];

        return view('Profile.Change_status', compact('user', 'statuses', 'statuseslt'));
    }

    public function ChangeStatus(Request $request)
    {
        $newstatus = $request->input('status');
        $userid = $request->input('userid');

        $status = DB::table('user_role')->select('id_User_role')->where('name', $newstatus)->first();

        $statusid = $status->id_User_role;

        DB::table('user')->where('id', $userid)->update(['role' => $statusid]);

        return redirect()->route('DisplayProfile', ['id' => $userid]);
    }
    public function DisplayBalance()
    {
        return view('Profile.Balance');
    }
}
