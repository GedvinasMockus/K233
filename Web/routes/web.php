<?php

use App\Http\Controllers\Main;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', function () {
    return view('Dash');
});

// User routes

Route::get('/Login', [UserController::class, 'DisplayLogin'])->name('Login')->middleware('guest');

Route::post('Validate_Login', [UserController::class, 'Validate_Login'])->name('Validate_Login');

Route::get('/Register', [UserController::class, 'DisplayRegister'])->name('Register')->middleware('guest');

Route::post('Validate_Reg', [UserController::class, 'Validate_Reg'])->name('Validate_Reg');

Route::get('/Verify-email/{token}', [UserController::class, 'Verify_Reg'])->name('Auth.Verify');

Route::Get('/Logout', [UserController::class, 'Logout'])->name('Logout')->middleware('auth');

Route::Get('/User_Profile', [UserController::class, 'DisplayUserProfile'])->name('DisplayUserProfile');

Route::post('/User_Profile/EditData', [UserController::class, 'Edit_user_data'])->name('Edit_user_data');

Route::post('/User_Profile/AddCar', [UserController::class, 'Add_Car'])->name('Add_Car');

Route::post('/User_Profile/EditCar', [UserController::class, 'Edit_Car'])->name('Edit_Car');

Route::post('/User_Profile/DeleteCar', [UserController::class, 'Delete_Car'])->name('Delete_Car');

Route::get('ShowCar', [UserController::class, 'ShowCarInfo'])->name('ShowCarInfo');

Route::get('GetUserCarInfoSingle/{id}', [UserController::class, 'GetUserCarInfoSingle'])->name('GetUserCarInfoSingle');

Route::get('GetUserCarInfoSingleSeparate/{id}', [UserController::class, 'GetUserCarInfoSingleSeparate'])->name('GetUserCarInfoSingleSeparate');

Route::get('GetUserInfo', [UserController::class, 'GetUserInfo'])->name('GetUserInfo')->middleware('auth')

Route::Get('/Profiles', [UserController::class, 'DisplayProfiles'])->name('DisplayProfiles');

Route::get('/Profile/{id}', [UserController::class, 'DisplayProfile'])->name('DisplayProfile');

Route::get('/Profile/{id}/ban', [UserController::class, 'BanUser'])->name('BanUser');

Route::get('/Profile/{id}/unban', [UserController::class, 'UnbanUser'])->name('UnbanUser');

Route::get('/Profile/{id}/change_status', [UserController::class, 'DisplayChangeStatus'])->name('DisplayChangeStatus');

Route::post('/Change_User_Status', [UserController::class, 'ChangeStatus'])->name('ChangeStatus');

Route::Get('/History', [UserController::class, 'DisplayHistory'])->name('DisplayHistory');

Route::Get('/Balance', [UserController::class, 'DisplayBalance'])->name('DisplayBalance');




// Reservation routes

Route::get('/Parking_Lots', [ReservationController::class, 'DisplayParkingLots'])->name('DisplayParkingLots');

Route::get('/Parking_Lot/Add', [ReservationController::class, 'DisplayNewParkingLot'])->name('DisplayNewParkingLot');

Route::get('/Parking_Lot/{id}', [ReservationController::class, 'DisplayParkingLot']);

Route::get('/Edit_Parking_Lot/{id}', [ReservationController::class, 'DisplayEditParkingLot']);

Route::get('/Resertvation', [ReservationController::class, 'DisplayReservations'])->name('DisplayReservations');

// Route::post('/Parking_Lot_AddLots', [ReservationController::class, 'SaveLots'])->name('SaveLots');

Route::post('/savelots', [ReservationController::class, 'SaveLots'])->name('SaveLots');









Route::Get('/Random', [Main::class, 'DisplayMain'])->name('Random')->middleware('auth');
