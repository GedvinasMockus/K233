<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationConfirmJob;
use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

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
            if ($user->role == 1) {
                $data = $request->input();
                $token = $user->generateEmailVerificationToken();
                $details['url'] = URL::signedRoute('Auth.Verify', ['token' => $token]);
                $details['email'] = $data['email'];

                dispatch(new SendRegistrationConfirmJob($details));
                return redirect('Login')->with('errorNotConfirmed', 'Jūsų paskyra dar nėra patvirtinta!')->withInput();
            }
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
            'phone' => 'required|regex:/(\+370)\d{8}/u|max:12',
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
            'phone.regex' => 'Blogas telefono numerio formatas!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return redirect('Register')
                ->withInput()
                ->withErrors($validator);
        } else {
            $data = $request->input();
            try {
                $user = new User;
                $user->name = $data['name'];
                $user->surname = $data['surname'];
                $user->email = $data['email'];
                $user->phone_number = $data['phone'];
                $user->password = Hash::make($data['password']);
                $user->uuid = Str::uuid();
                $user->balance = 0.00;
                $user->role = 1;
                $user->save();
                $token = $user->generateEmailVerificationToken();
                $details['url'] = URL::signedRoute('Auth.Verify', ['token' => $token]);
                $details['email'] = $data['email'];

                dispatch(new SendRegistrationConfirmJob($details));

                return redirect('Login')->with('success', 'Registracija sėkminga!');
            } catch (Exception $e) {
                return redirect('Register')->with('failed', 'Registracija nesėkminga! Bandykite iš naujo.')->withInput();
            }
        }
    }

    public function Verify_reg(Request $request)
    {
        $token = substr($request->url(), strrpos($request->url(), '/') + 1);
        $cacheKey = 'email_verification_' . $token;
        $userId = Cache::get($cacheKey);
        if ($userId) {
            $user = User::find($userId);
            $user->role = 2;
            $user->save();
            Cache::forget($cacheKey);
            return redirect('Login')->with('success', 'Paskyra patvirtinta sėkmingai!');
        } else {
            return redirect('Login')->with('error', 'Paskyra nebuvo patvirtinta! Tai galėjo nutikti: <br>
            1. Paskyra jau patvirtinta. <br>
            2. Baigėsi laikas patvirtinti paskyrą. Prašome prisijungti iš naujo, kad naujas patvirtinimmo laiškas būtų išsiųstas!');
        }
    }

    public function Logout()
    {
        Session::flush();
        return Redirect('');
    }

    public function DisplayUserProfile()
    {
        $userController = new UserController();
        return view('Profile.User_profile')->with(["cars" =>  $userController->GetUserCarInfo()]);
    }

    public function Edit_user_data(Request $request)
    {
        $rules = [
            'name' => 'required|alpha|min:3|max:20',
            'surname' => 'required|alpha|min:3|max:20',
            'phone' => 'required|regex:/(\+370)\d{8}/u|max:12',
        ];
        $customMessages = [
            'required' => 'Laukelis turi būti užpildytas!',
            'alpha' => 'Laukelyje gali būti tik raidės!',
            'min' => 'Laukelyje esanti informacija per trumpa!',
            'max' => 'Laukelyje esanti informacija per ilga!',
            'phone.regex' => 'Blogas telefono numerio formatas!'
        ];

        $user = auth()->user();
        if (!empty($request->oldPassword) || !empty($request->password) || !empty($request->password_confirmation)) {
            $rules['password'] = 'required|min:8|confirmed';
            $rules['oldPassword'] = ['required', function ($attribute, $value, $fail) use ($user, $request) {
                if (!@Hash::check($request->oldPassword, $user->password)) {
                    return $fail('Įvestas slaptažodis nesutampa su sistemoje išsaugotu slaptažodžiu!');
                }
            }];
            $customMessages['confirmed'] = 'Slaptažodžiai nesutampa!';
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
            } else {
                $data = $request->input();
                DB::update('update user set name=?, surname=?, phone_number=?, password=? where id = ?', [$request->name, $request->surname, $request->phone, Hash::make($request->password), $user->id]);
                return response()->json(['status' => 1, 'data' => $data]);
            }
        } else {
            $validator = Validator::make($request->all(), $rules, $customMessages);
            if ($validator->fails()) {
                return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
            } else {
                $data = $request->input();
                DB::update('update user set name=?, surname=?, phone_number=? where id = ?', [$request->name, $request->surname, $request->phone, $user->id]);
                return response()->json(['status' => 1, 'data' => $data]);
            }
        }
    }

    public function Add_Car(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'manufacturer' => 'required|alpha|min:2|max:20',
            'model' => 'required|alpha_numbers_spaces_minus|min:3|max:20',
            'year' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
            'number' => 'required|alpha_num_dash'
        ];
        $customMessages = [
            'required' => 'Laukelis turi būti užpildytas!',
            'alpha' => 'Laukelyje gali būti tik raidės!',
            'alpha_numbers_spaces_minus' => 'Laukelyje esanti informacija netinkama!',
            'min' => 'Laukelyje esanti informacija per trumpa!',
            'max' => 'Laukelyje esanti informacija per ilga!',
            'year.min' => 'Laukelyje esantys metai yra senesni nei 1950!',
            'year.max' => 'Laukelyje esantys metai yra jaunesni nei ' . date('Y') . '!',
            'year.digits' => 'Laukelyje esantys metai netinkamo formato!',
            'numeric' => 'Laukelyje esanti informacija gali būti tik skaičiai!',
            'alpha_num_dash' => 'Laukelyje esantys valstybinis numeris yra netinkamo formato!',

        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $car = $request->manufacturer . ', ' . $request->model . ', ' . $request->year;
            DB::insert('insert into car (fk_Userid, car_name, license_plate) values (?, ?, ?)', [$user->id, $car, $request->number]);
            $userController = new UserController();
            return response()->json(['status' => 1, 'car' => $userController->GetUserCarInfo()]);
        }
    }

    public function Edit_Car(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'id' => ['exists:car,id', function ($attribute, $value, $fail) use ($user, $request) {
                if (sizeof(DB::table('car')->select(['car.*'])->where('fk_Userid', '=', $user->id)->where('id', '=', $request->id)->get()) == 0) {
                    return $fail('Automobilis nerastas!');
                }
            }],
            'manufacturer' => 'required|alpha|min:2|max:20',
            'model' => 'required|alpha_numbers_spaces_minus|min:3|max:20',
            'year' => 'required|numeric|digits:4|min:1950|max:' . date('Y'),
            'number' => 'required|alpha_num_dash'
        ];
        $customMessages = [
            'required' => 'Laukelis turi būti užpildytas!',
            'alpha' => 'Laukelyje gali būti tik raidės!',
            'alpha_numbers_spaces_minus' => 'Laukelyje esanti informacija netinkama!',
            'min' => 'Laukelyje esanti informacija per trumpa!',
            'max' => 'Laukelyje esanti informacija per ilga!',
            'year.min' => 'Laukelyje esantys metai yra senesni nei 1950!',
            'year.max' => 'Laukelyje esantys metai yra jaunesni nei ' . date('Y') . '!',
            'year.digits' => 'Laukelyje esantys metai netinkamo formato!',
            'numeric' => 'Laukelyje esanti informacija gali būti tik skaičiai!',
            'alpha_num_dash' => 'Laukelyje esantys valstybinis numeris yra netinkamo formato!',
            'exists' => 'Automobilis nerastas!'

        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $car = $request->manufacturer . ', ' . $request->model . ', ' . $request->year;
            DB::update('update car set car_name=?, license_plate=? where id = ?', [$car, $request->number, $request->id]);
            $userController = new UserController();
            return response()->json(['status' => 1, 'car' => $userController->GetUserCarInfo()]);
        }
    }

    public function Delete_Car(Request $request)
    {
        $user = auth()->user();
        $rules = [
            'idd' => ['exists:car,id', function ($attribute, $value, $fail) use ($user, $request) {
                if (sizeof(DB::table('car')->select(['car.*'])->where('fk_Userid', '=', $user->id)->where('id', '=', $request->idd)->get()) == 0) {
                    return $fail('Automobilis nerastas!');
                }
            }],
        ];
        $customMessages = [
            'exists' => 'Automobilis nerastas!'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            DB::delete('delete from car where id = ?', [$request->idd]);
            $userController = new UserController();
            return response()->json(['status' => 1, 'car' => $userController->GetUserCarInfo()]);
        }
    }

    public function GetUserCarInfo()
    {
        $user = auth()->user();
        $car = DB::table('car')
            ->select(['id', 'car_name', 'license_plate'])
            ->where('fk_Userid', '=', $user->id)->get();
        return $car;
    }

    public function ShowCarInfo()
    {
        $userController = new UserController();
        return view('Profile.Cars.ShowCars')->with(['cars' => $userController->GetUserCarInfo()])->render();
    }

    public function GetUserCarInfoSingle($id)
    {
        $user = auth()->user();
        $data = DB::table('car')
            ->select(['id', 'car_name', 'license_plate'])
            ->where('fk_Userid', '=', $user->id)
            ->where('id', '=', $id)
            ->first();
        return response()->json(["singleCar" => $data]);
    }
    public function GetUserCarInfoSingleSeparate($id)
    {
        $user = auth()->user();
        $data = DB::table('car')
            ->select(['id', 'car_name', 'license_plate'])
            ->where('fk_Userid', '=', $user->id)
            ->where('id', '=', $id)
            ->first();
        $parts = explode(', ', $data->car_name);
        $make = $parts[0];
        $model = $parts[1];
        $year = $parts[2];
        $dataObject = new \stdClass();
        $dataObject->make = $make;
        $dataObject->model = $model;
        $dataObject->year = $year;
        $dataObject->license_plate = $data->license_plate;
        return response()->json(["singleCar" => $dataObject]);
    }

    public function GetUserInfo()
    {
        $user = auth()->user();
        $data = DB::table('user')
            ->select(['user.name', 'user.surname', 'user.phone_number'])
            ->where('id', '=', $user->id)->first();
        return response()->json(["data" => $data]);
    }

    public function DisplayProfiles()
    {
        $users = DB::table('user')->select('id', 'name', 'surname', 'email')->get();
        // dd($users);

        return view('Profile.Profiles', ['users' => $users]);
    }

    public function DisplayProfile($id)
    {
        if (DB::table('user')->where('id', $id)->first()->role == 3) {
            $isblocked = True;
        } else {
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
        $user = DB::table('user')->where('id', $id)->first();
        $statuses = DB::table('user_role')->where('id_User_role', '!=', 3)->get();

        $statuseslt = ['Nepatvirtintas vartotojas', 'Paprastas vartotojas', 'Administratorius'];

        return view('Profile.Change_status', compact('user', 'statuses', 'statuseslt'));
    }

    public function ChangeStatus(Request $request)
    {
        $newstatus = $request->input('status');
        $userid = $request->input('userid');

        DB::table('user')->where('id', $userid)->update(['role' => $newstatus]);

        return redirect()->route('DisplayProfile', ['id' => $userid]);
    }
    public function DisplayBalance()
    {
        return view('Profile.Balance');
    }
}
