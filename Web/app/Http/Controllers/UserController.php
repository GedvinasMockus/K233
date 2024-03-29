<?php

namespace App\Http\Controllers;

use App\Jobs\SendRegistrationConfirmJob;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use App\Rules\PaymentTimeRule;
use Exception;
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
use WebToPay;

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
        $user = Auth::user();
        $reservations = Reservation::getHistory($user->id);
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
                'backgroundColor' =>  "grey",
                'title' => "Jūsų rezervacija",
                'extendedProps' => $description,
            ];
        }
        $events = json_encode($events);
        return view('Profile.History')->with(['events' => $events]);
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
    public function Add_balance(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'sum' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/', 'min:1', 'max:1000', new PaymentTimeRule]
        ];
        $customMessages = [
            'required' => 'Privaloma įrašyti sumą!',
            'numeric' => 'Blogas sumos formatas!',
            'regex' => 'Blogas sumos formatas!',
            'min' => 'Mažiausia balanso pildymo suma 1€!',
            'max' => 'Daugiausiai galima pridėti prie balanso 1000€'
        ];
        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'error' => $validator->errors()->toArray()]);
        } else {
            $data = $request->input();
            $payment = new Payment;
            $payment->price = $data['sum'];
            $payment->date = date('Y-m-d H:i:s', time());
            $payment->status = 1;
            $payment->fk_Userid = $user->id;
            $payment->save();
            $id = $payment->id;
            $padded_id = str_pad($id, 7, '0', STR_PAD_LEFT);
            $pay = WebToPay::buildRequest([
                'projectid' => '236497',
                'sign_password' => '4f73fdc9fa66f6d72cebf14a7a85b653',
                'orderid' => $padded_id,
                'amount' => $data['sum'] * 100,
                'p_email'        => $user->email,
                'p_firstname'    => $user->name,
                'p_lastname'    => $user->surname,
                'currency' => 'EUR',
                'country' => 'LT',
                'accepturl' => url('/') . "/accept",
                'cancelurl' => url('/') . "/cancel/$id",
                'callbackurl' => url('/') . "/callback",
                'test' => 1,
            ]);
            $payLink = "https://bank.paysera.com/pay/?data=" . $pay['data'] . "&sign=" . $pay['sign'];
            return response()->json(['status' => 1, 'data' => $payLink]);
        }
    }
    public function Accept()
    {
        return redirect("/")->with('successMes', 'Apmokėjimas patvirtintas!');
    }

    public function Cancel($id)
    {
        $updatePayment = Payment::find($id);
        $updatePayment->status = 2;
        $updatePayment->save();
        return redirect("/")->with('errorMes', 'Apmokėjimas nesėkmingas!');
    }

    public function Callback(Request $request)
    {
        try {
            $response = WebToPay::validateAndParseData(
                $request->all(),
                '236497',
                '4f73fdc9fa66f6d72cebf14a7a85b653'
            );
            $updatePayment = Payment::find(intval($response['orderid']));
            $updatePayment->status = 3;
            $updatePayment->save();
            $updateUser = User::find($updatePayment->fk_Userid);
            $updateUser->balance = $updateUser->balance + $updatePayment->price;
            $updateUser->save();
            echo 'OK';
        } catch (Exception $exception) {
            $updatePayment = Payment::find(intval($response['orderid']));
            $updatePayment->status = 2;
            $updatePayment->save();
            echo 'BAD';
        }
    }
    public function UserSearch(Request $request)
    {
        $input =  $request->input('input');
        $users = User::where('name', 'like', "%$input%")
            ->orWhere('surname', 'like', "%$input%")
            ->orWhere('email', 'like', "%$input%")
            ->limit(10)
            ->get(['id', 'name', 'surname', 'email']);
        return response()->json(['items' => $users]);
    }
}
