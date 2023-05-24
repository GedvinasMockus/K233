<?php

use App\Http\Controllers\Main;
use App\Http\Controllers\AdminController;
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

Route::get('/Verify-email/{token}', [UserController::class, 'Verify_Reg'])->name('Auth.Verify')->middleware('guest');

Route::Get('/Logout', [UserController::class, 'Logout'])->name('Logout')->middleware('auth')->middleware('rolecheck:2,4');

Route::Get('/User_Profile', [UserController::class, 'DisplayUserProfile'])->name('DisplayUserProfile')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/User_Profile/EditData', [UserController::class, 'Edit_user_data'])->name('Edit_user_data')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/User_Profile/AddCar', [UserController::class, 'Add_Car'])->name('Add_Car')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/User_Profile/EditCar', [UserController::class, 'Edit_Car'])->name('Edit_Car')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/User_Profile/DeleteCar', [UserController::class, 'Delete_Car'])->name('Delete_Car')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('ShowCar', [UserController::class, 'ShowCarInfo'])->name('ShowCarInfo')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('GetUserCarInfoSingle/{id}', [UserController::class, 'GetUserCarInfoSingle'])->name('GetUserCarInfoSingle')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('GetUserCarInfoSingleSeparate/{id}', [UserController::class, 'GetUserCarInfoSingleSeparate'])->name('GetUserCarInfoSingleSeparate')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('GetUserInfo', [UserController::class, 'GetUserInfo'])->name('GetUserInfo')->middleware('auth')->middleware('rolecheck:2,4');

Route::Get('/Profiles', [UserController::class, 'DisplayProfiles'])->name('DisplayProfiles')->middleware('auth')->middleware('rolecheck:4');

Route::get('/Profile/{id}', [UserController::class, 'DisplayProfile'])->name('DisplayProfile')->middleware('auth')->middleware('rolecheck:4');

Route::get('/Profile/{id}/ban', [UserController::class, 'BanUser'])->name('BanUser')->middleware('auth')->middleware('rolecheck:4');

Route::get('/Profile/{id}/unban', [UserController::class, 'UnbanUser'])->name('UnbanUser')->middleware('auth')->middleware('rolecheck:4');

Route::get('/Profile/{id}/change_status', [UserController::class, 'DisplayChangeStatus'])->name('DisplayChangeStatus')->middleware('auth')->middleware('rolecheck:4');

Route::post('/Change_User_Status', [UserController::class, 'ChangeStatus'])->name('ChangeStatus')->middleware('auth')->middleware('rolecheck:4');

Route::Get('/History', [UserController::class, 'DisplayHistory'])->name('DisplayHistory')->middleware('auth')->middleware('rolecheck:2,3,4');

Route::Post('/Add_Balance', [UserController::class, 'Add_balance'])->name('Add_balance')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('/accept', [UserController::class, 'Accept'])->name('Accept');

Route::get('/cancel/{id}', [UserController::class, 'Cancel'])->name('Cancel');

Route::get('/callback', [UserController::class, 'Callback'])->name('Callback');

Route::get('/search_user', [UserController::class, 'UserSearch'])->name('UserSearch')->middleware('auth')->middleware('rolecheck:4');



// Reservation routes

Route::get('/Parking_Lots', [ReservationController::class, 'DisplayParkingLots'])->name('DisplayParkingLots');

Route::get('/Parking_Lot/Add', [ReservationController::class, 'DisplayNewParkingLot'])->name('DisplayNewParkingLot')->middleware('auth')->middleware('rolecheck:4');

Route::get('/Parking_Lot/{id}', [ReservationController::class, 'DisplayParkingLot']);

Route::get('/Parking_Space/{id}', [ReservationController::class, 'DisplayParkingSpace']);

Route::get('/Edit_Parking_Lot/{id}', [ReservationController::class, 'DisplayEditParkingLot'])->middleware('auth')->middleware('rolecheck:4')->middleware('rolecheck:4');

Route::get('/Reservation', [ReservationController::class, 'DisplayReservations'])->name('DisplayReservations')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/RemoveReservation', [ReservationController::class, 'RemoveReservation'])->name('RemoveReservation')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('/Parking_Reservation_Admin/{id}', [ReservationController::class, 'UserReservation'])->name('UserReservation')->middleware('auth')->middleware('rolecheck:4');

Route::post('/savelots', [ReservationController::class, 'SaveLots'])->name('SaveLots')->middleware('auth')->middleware('rolecheck:4');

Route::post('/MakeReservation', [ReservationController::class, 'MakeReservation'])->name('MakeReservation')->middleware('auth')->middleware('rolecheck:2,4');

Route::post('/MakeUserReservation', [ReservationController::class, 'MakeUserReservation'])->name('MakeUserReservation')->middleware('auth')->middleware('rolecheck:4');

Route::post('/UpdateReservation', [ReservationController::class, 'UpdateReservation'])->name('UpdateReservation')->middleware('auth')->middleware('rolecheck:2,4');

Route::get('/EditReservation/{id}', [ReservationController::class, 'EditReservation'])->name('EditReservation')->middleware('auth')->middleware('rolecheck:2,4');

// Admin routes

Route::Get('/DataReport', [AdminController::class, 'DisplayDataReport'])->name('DisplayDataReport')->middleware('auth')->middleware('rolecheck:4');

Route::post('/generatedatareport', [AdminController::class, 'GenerateDataReport'])->name('GenerateDataReport')->middleware('auth')->middleware('rolecheck:4');

Route::Get('/reports', [AdminController::class, 'DisplayReports'])->name('DisplayReports')->middleware('auth')->middleware('rolecheck:4');

Route::post('/answerReport', [AdminController::class, 'AnswerToReport'])->name('AnswerToReport')->middleware('auth')->middleware('rolecheck:4');
