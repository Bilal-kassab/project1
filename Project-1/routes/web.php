<?php

use App\Http\Controllers\AirportController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StaticBookController;
use App\Mail\TestMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

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

// Route::get('/', function () {
//     Mail::to('bilalkassab70@gmail.com')->send(new TestMail());

//     return "Done send";
// });


Route::prefix('user')->group(function () {
    Route::controller(CountryController::class)->group(function(){
        Route::get('show_country/{id}','show');
        Route::get('get_all_country','index');
    });
});

Route::prefix('admin')->group(function () {
    Route::controller(CountryController::class)->group(function(){
        Route::get('show_country/{id}','show');
        Route::get('get_all_country','index');
    });
});

Route::controller(StaticBookController::class)->group(function(){

    Route::get('show-static-trip/{id}','showStaticTrip');
    Route::get('all-static-trip','index');
});
Route::controller(AirportController::class)->group(function(){

    Route::get('airport-trip/{id}','airportTrip');
});

