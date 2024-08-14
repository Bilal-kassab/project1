<?php

use App\Events\PushWebNotification;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\StaticBookController;
use App\Listeners\SendWebNotification;
use App\Mail\TestMail;
use App\Models\Booking;
use App\Models\BookPlace;
use App\Models\Place;
use App\Models\PlaneTrip;
use App\Models\RateBooking;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
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
    // Mail::to('bilalkassab70@gmail.com')->send(new TestMail());

    // return "Done send";
    // return $date=Carbon::now()->format('Y-m-d');
// });
Route::get('/', function () {
    // $book=Booking::where('type','static')
    //             ->AvailableRooms()->with('places')
    //             ->findOrFail(1);
    // $places=BookPlace::where('book_id',$book->id)->with('places')->get();

    $totalRatings = RateBooking::where('booking_id', 2)->sum('rate')== 0 ? 1:1;
    $ratingsCount = RateBooking::where('booking_id', 2)->count() == 0 ? 1:1;
    return $totalRatings/$ratingsCount;
    // $result = [
    //     'trip_id' => $book->id,
    //     'trip_name' => $book->name,
    //     'places' => $book->placesss->map(function($place) {
    //         return [
    //             'id' => $place->id,
    //             'name' => $place->name,
    //             'current_price' => $place->pivot->current_price,
    //             'text' => $place->text,
    //             'area_id' => $place->area_id,
    //             'visible' => $place->visible,
    //         ];
    //     })
    // ];

    // return $result['places'];
});

// Route::post('/', function (Request $request) {
//     // return Lang::locale();
//     $topPlaces = Place::withCount('bookings')
//                         ->whereHas('bookings')
//                         ->orderBy('bookings_count', 'desc')
//                         ->take(10)
//                         ->get();

//     return $topPlaces;
// })->middleware('auth:sanctum');

// Route::get('/', function () {

//     try{
//         $apiUrl = 'https://fcm.googleapis.com/v1/projects/test-49b2e/messages:send';
//         $access_token = Cache::remember('access_token', now()->addHour(), function () use ($apiUrl) {
//             $credentialsFilePath = storage_path('app/fcm.json');
//             // dd($credentialsFilePath);
//             $client = new \Google_Client();
//             $client->setAuthConfig($credentialsFilePath);
//             $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
//             // dd($client);
//             $client->fetchAccessTokenWithAssertion();
//             $token = $client->getAccessToken();
//             return $token['access_token'];
//         });
//         $fcm_token='exUehn31-o1OCef9EGnyZb:APA91bG7fofNirxQ0b4X5SJdZEHw3CpuoFgitsuLcSy9B2JTEQZxtXlYJuaCXUT6jpdjVG8CsP6JfJyQwYEJ1BH2ffq1CNHvm5UV4_ZgB-vskWUgqwVXDwD7_DhsX9lZMyG-GGx5zOPt';
//         $message = [
//             "message" => [
//                 "token" => $fcm_token,
//                 "notification" => [
//                     "title" => 'title',
//                     "body" => 'text',
//                 ]
//             ]
//         ];

//         $response = Http::withHeader('Authorization', "Bearer $access_token")->post($apiUrl, $message);
//         dd($response);
//         return "sent";

//     }catch(Exception $ex){
//         return response()->json([
//             'message'=>$ex->getMessage(),
//         ]);
//     }

// });


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

