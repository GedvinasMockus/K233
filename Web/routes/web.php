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

Route::Get('/Logout', [UserController::class, 'Logout'])->name('Logout')->middleware('auth');

Route::Get('/User_Profile', [UserController::class, 'DisplayUserProfile'])->name('DisplayUserProfile');

Route::Get('/Profiles', [UserController::class, 'DisplayProfiles'])->name('DisplayProfiles');

Route::get('/Profile/{id}', [UserController::class, 'DisplayProfile']);

Route::Get('/History', [UserController::class, 'DisplayHistory'])->name('DisplayHistory');

Route::Get('/Balance', [UserController::class, 'DisplayBalance'])->name('DisplayBalance');



// Reservation routes

Route::get('/Parking_Lots', [ReservationController::class, 'DisplayParkingLots'])->name('DisplayParkingLots');

Route::get('/Parking_Lot/{id}', [ReservationController::class, 'DisplayParkingLot']);

Route::get('/Edit_Parking_Lot/{id}', [ReservationController::class, 'DisplayEditParkingLot']);

Route::get('/Resertvation', [ReservationController::class, 'DisplayReservations'])->name('DisplayReservations');












Route::Get('/Random', [Main::class, 'DisplayMain'])->name('Random')->middleware('auth');