<?php

use App\Http\Controllers\CountryController;
use App\Http\Controllers\StaticBookController;
use App\Models\Booking;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

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

               $book=Booking::where('type','static')
                        ->userRooms(2,null)
                        ->findOrFail(1);

                        $bookData=[
                            'id'=>$book['id'],
                            'source_trip_id'=>$book['source_trip_id'],
                            'destination_trip_id'=>$book['destination_trip_id'],
                            'trip_name'=>$book['trip_name'],
                            'price'=>$book['price'],
                            'number_of_people'=>$book['number_of_people'],
                            // 'trip_capacity'=>$book['trip_capacity'],
                            'start_date'=>$book['start_date'],
                            'end_date'=>$book['end_date'],
                            // 'stars'=>$book['stars'],
                            'trip_note'=>$book['trip_note'],
                            'type'=>$book['type'],
                            // 'rooms_count'=>$book['rooms_count'],
                        ];
            return $bookData;
});


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

