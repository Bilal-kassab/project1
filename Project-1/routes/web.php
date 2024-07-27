<?php

use App\Events\PushWebNotification;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\StaticBookController;
use App\Listeners\SendWebNotification;
use App\Mail\TestMail;
use App\Models\Booking;
use App\Models\PlaneTrip;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

Route::get('/', function () {

    $user=User::where('id',8)->first();
    $user->givePermissionTo('banned');
    // $user->revokePermissionTo('unbanned');
    // if($user->hasPermissionTo('banned')){
    // // if($user->hasRole('User')){
    //     return "banned";
    // }

    return empty($user['permissions'][0]);

});
// Route::get('/', function () {

//     $planetrip=PlaneTrip::where('id',1)->first();
//         $date=new Carbon($planetrip['flight_date']);
//     //     return $date->addHours(48)->format('Y-m-d');
//     // $date= new DateTime(Carbon::now()->addHours(24));
//     // $date2= new DateTime(Carbon::now());
//     // $x= $date->diff($date2);
//         $nowDate=Carbon::now()->format('Y-m-d');
//         $date= new Carbon($nowDate);
//         $data=[
//             'flight_date'=>$date->format('Y-m-d'),
//             'landing_date'=>$date->addDays(1)->format('Y-m-d')
//         ];
//     // $data=[
//     //     '1'=>$planetrip['flight_date'],
//     //     '2'=>$date->addDays(2)->format('Y-m-d'),
//     //     '3'=>$date->diffInHours($planetrip['flight_date']),
//     // ];
//     return $data ;
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
Route::controller(FavoriteController::class)->group(function(){

    Route::get('getSuggest','getSuggest');
});

