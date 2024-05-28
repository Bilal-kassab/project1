<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DynamicBookController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\PlaneController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\StaticBookController;
use Illuminate\Support\Facades\Route;




################       users      ###########################


Route::post('register',[UserController::class,'register']);
Route::post('login',[UserController::class,'login']);

Route::post('confirm-Code',[UserController::class,'confirmCode']);
Route::post('forget-password',[UserController::class,'forgetPassword_SendEmail']);
Route::post('set-new-password',[UserController::class,'forgetPassword_SetPassword']);


Route::group(['middleware'=>['auth:sanctum']], function () {
    /*
    Route::get('check', function () {
        return response()->json([
            'Message'=> 'Check',
        ],200);
    });
    */

    Route::get('logout',[UserController::class,'logout']);
    Route::get('profile',[UserController::class,'profile']);

    Route::controller(FavoriteController::class)->group(function () {
        Route::post('set-favorite','setFavorite');
        Route::post('delete-favorite','deleteFavorite');
        Route::get('get-all-favorite','index');
    });

    Route::prefix('user')->group(function () {

        Route::controller(UserController::class)->group(function () {
            Route::post('update-profile','updateProfile');
            Route::post('delete-profile-photo','deleteProfilePhoto');
            Route::post('change-profile-photo','changeProfilePhoto');
        });

        Route::controller(CountryController::class)->group(function(){
            Route::get('show_country/{id}','show');
            Route::get('get_all_country','index');
            Route::post('search-for-country','search');
        });

        Route::controller(CategoryController::class)->group(function(){
            Route::get('get_all_category','index');
            Route::get('show_category/{id}','show');
            Route::post('search-for-category','search');
        });

        Route::controller(PlaceController::class)->group(function () {
            Route::get('places','index');
            Route::get('show/{id}','show');
            Route::get('places-depending-on-area/{id}','placesDependingOnArea');
            Route::get('places-depending-on-country/{id}','placesDependingOnCountry');
            Route::get('places-depending-on-category/{id}','placesDependingOnCategory');
            Route::get('places-depending-on-position','placesDependingOnPosition');
            Route::post('search-for-place','search');
        });

        Route::controller(AreaController::class)->group(function(){
            Route::post('get_all_area','getAreasForCountry');
            Route::get('show_area/{id}','show');
            Route::post('search-for-area','search');
         });

         Route::controller(AirportController::class)->group(function(){
            Route::get('show-airport/{id}','show');
            Route::post('search-for-airport','search');
            Route::get('get-all-country-airports/{id}','getAllCountryAirports');
            Route::get('get-all-area-airports/{id}','getAllAreaAirports');
         });


         Route::controller(AirportController::class)->group(function(){
            Route::post('search-for-airport','search');
            Route::get('show-airport/{id}','show');
            Route::get('get-all-country-airports/{id}','getAllCountryAirports');
            Route::get('get-all-area-airports/{id}','getAllAreaAirports');
         });

        Route::controller(PlaneController::class)->group(function(){
            Route::post('search-for-plane-trip','searchForPlaneTrip');
            Route::get('show-plane-trip-details/{id}','showPlaneTripDetails');
        });

         Route::controller(HotelController::class)->group(function(){
            Route::get('get_all_Hotel','index');
            Route::get('show_hotel/{id}','show');
            Route::post('search_Hotel_by_name','search_Hotel_by_Name');
            Route::post('search_Hotel_by_stars','search_Hotel_by_Stars');
            Route::get('get_Hotel_By_Area/{id}','get_hotel_in_area');
            Route::get('get_Hotel_By_Country/{id}','get_hotel_in_country');

         });
         Route::controller(RoomController::class)->group(function(){
            Route::get('show_room/{id}','show');
            Route::get('get_all_room/{id}','index');
        });
        Route::controller(CommentController::class)->group(function(){
            Route::post('add-comment','setComment');
            Route::get('show-all-place-comments/{id}','showAllPlaceComment');
        });

    });

});




################       Admin      ###########################

Route::post('admin-login',[AdminController::class,'login'])->middleware('approve-admin');
Route::post('add-admin',[AdminController::class,'addAdmin']);
Route::controller(RoleController::class)->group(function () {
    Route::get('get-all-roles','getAllRoles');
});
Route::group(['middleware'=>['auth:sanctum','role:Super Admin|Trip manger|Hotel admin|Airport admin|Admin']],function(){

        Route::controller(AdminController::class)->group(function () {
            Route::get('admin-profile','profile');
            Route::get('admin-logout','logout');
            Route::post('change-profile-photo','changeProfilePhoto');
            Route::post('filter','filter');
            Route::get('get-admin/{id}','getAdmin');
            Route::get('admins-requests','adminsRequests');
            Route::get('get-admis-for-role/{id}','getAdmisForRole');
            Route::post('delete-profile-photo','deleteProfilePhoto');
            Route::post('approve-user','approveUser');
            Route::post('update-profile','updateProfile');
        });


        Route::controller(RoleController::class)->group(function () {
            Route::get('get-all-permission','getAllPermission');
            Route::get('get-all-permission-for-role/{id}','getAllPermissionForRole');
            Route::post('add-role','addRole');
            // put this api in postman
            Route::post('ban-user','banUser');
            Route::post('un-ban-User','unbanUser');
        });



        Route::prefix('admin')->group(function () {


            Route::controller(PlaceController::class)->group(function () {
                Route::post('add-place','store');
                Route::get('places','index');
                Route::get('show/{id}','show');
                Route::post('change-exist-place-image','updateExistPlaceImage');
                Route::post('add-place-image','addPlaceImage');
                Route::post('update-place/{id}','updatePlace');
                Route::get('places-depending-on-area/{id}','placesDependingOnArea');
                Route::get('places-depending-on-country/{id}','placesDependingOnCountry');
                Route::get('places-depending-on-category/{id}','placesDependingOnCategory');
                Route::get('places-depending-on-position','placesDependingOnPosition');
                Route::post('search-for-place','search');
            });

            Route::controller(CountryController::class)->group(function(){
                Route::post('store_country','store');
                Route::get('show_country/{id}','show');
                Route::get('get_all_country','index');
                Route::post('update_country/{id}','update');
                Route::get('delete_country/{id}','destroy');
                Route::post('search-for-country','search');
            });

            Route::controller(CategoryController::class)->group(function(){
                Route::post('store_category','store');
                Route::get('get_all_category','index');
                Route::get('show_category/{id}','show');
                Route::post('update_category/{id}','update');
                Route::post('delete_category/{id}','destroy');
                Route::post('search-for-category','search');
            });

            Route::controller(AreaController::class)->group(function(){
                Route::post('store_area','store');
                Route::post('get_all_area','getAreasForCountry');
                Route::get('show_area/{id}','show');
                Route::post('update_area/{id}','update');
                Route::post('delete_area/{id}','destroy');
                Route::post('search-for-area','search');
             });

             Route::controller(AirportController::class)->group(function(){
                Route::post('add-airport','store');
                Route::post('update-airport/{id}','update');
                Route::post('delete-airport/{id}','destroy');
                Route::post('search-for-airport','search');
                Route::get('get-my-airport','getMyAirport');
                Route::get('all-airport','allAirport');
                Route::get('get-airport-details/{id}','getAirportDetails');
                Route::get('show-airport/{id}','show');
                Route::get('get-all-country-airports/{id}','getAllCountryAirports');
                Route::get('get-all-area-airports/{id}','getAllAreaAirports');
             });

            Route::controller(PlaneController::class)->group(function(){
                Route::post('add-plane','store');
                Route::post('update-plane/{id}','update');
                Route::post('add-trip','addTrip');
                Route::post('search-for-plane-trip','searchForPlaneTrip');
                Route::post('update-exist-plane-image','updateExistPlaneImage');
                Route::post('add-plane-image','addPlaneImage');
                Route::get('get-my-planes','getMyPlane');
                Route::get('get-all-plane-admin-trip','getAllPlaneAdminTrip');
                Route::get('get-all-plane-trip','getAllPlaneTrip');
                Route::get('show-plane-trip-details/{id}','showPlaneTripDetails');
            });

            Route::controller(HotelController::class)->group(function(){
                Route::get('get_all_Hotel','index');
                Route::post('add_hotel','store');
                Route::post('update_hotel/{id}','update');
                Route::post('change-exist-hotel-image','update_Image_Hotel');
                Route::get('show_hotel/{id}','show');
                Route::post('search_Hotel_by_name','search_Hotel_by_Name');
                Route::post('search_Hotel_by_stars','search_Hotel_by_Stars');
                Route::get('get_Hotel_By_Area/{id}','get_hotel_in_area');
                Route::get('get_Hotel_By_Country/{id}','get_hotel_in_country');
                Route::get('delete_hotel/{id}','destroy');
                Route::post('change_visible','changeVisible');
            });

            Route::controller(RoomController::class)->group(function(){
                Route::post('Add_rooms','store');
                Route::get('show_room/{id}','show');
                Route::post('update_rooms','update');
                Route::get('delete_room/{id}','destroy');
                Route::post('get_all_room/{id}','index');
                Route::get('get_my_rooms','get_My_Rooms');
                Route::post('change_status_room','change_status_room');
                Route::post('booking_room','booking_room');
            });

            Route::controller(StaticBookController::class)->group(function(){
                Route::post('Add_booking_Admin','store_Admin');
                Route::post('edit-static-trip/{id}','update_Admin');
                Route::get('all-static-trip','index');
                Route::get('show-static-trip/{id}','showStaticTrip');
            });
            Route::controller(DynamicBookController::class)->group(function(){
                Route::post('Add_booking_User','store_User');
                Route::get('All_booking','index');
                Route::get('show_booking/{id}','show');
                Route::post('hotel_book','hotel_book');
                Route::post('plane_book','plane_book');
                Route::get('all_my_dynamic_trip','index');
                Route::get('show_dynamic_trip/{id}','showDynamicTrip');
                Route::get('show_hotel_trip/{id}','showHotelTrip');
                Route::get('show_plane_trip/{id}','showPlaneTrip');
            });
            Route::controller(CommentController::class)->group(function(){
                Route::post('add-comment','setComment');
                Route::get('show-all-place-comments/{id}','showAllPlaceComment');
            });
            Route::controller(ActivityController::class)->group(function(){
                Route::post('add-activity','addActivity');
                Route::post('search-activity','searchActivity');
                Route::get('get-all-activity','getAllActivity');
            });
        });


    });

