<?php

use App\Http\Controllers\CountryController;
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

    return Carbon::now()->format('Y-m-d');
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

