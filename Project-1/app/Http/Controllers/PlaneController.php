<?php

namespace App\Http\Controllers;

use App\Helpers\ImageProcess;
use App\Models\Airport;
use App\Models\AirportImage;
use App\Models\BookPlane;
use App\Models\Country;
use App\Models\Plane;
use App\Models\PlaneTrip;
use App\Repositories\Interfaces\PlaneTripRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class PlaneController extends Controller
{
    private $planetriprepository;

    public function __construct(PlaneTripRepositoryInterface $planetriprepository)
    {
        $this->planetriprepository = $planetriprepository;
        $this->middleware('role:Admin|Airport admin', ['only'=> ['addTrip','getMyPlane','store','update','updateExistPlaneImage','addPlaneImage']]);
        $this->middleware('role:Super Admin|Admin|Airport admin', ['only'=> ['getAirportDetails']]);
        $this->middleware('role:Super Admin|Trip manger', ['only'=> ['getAllPlaneTrip']]);
    }

    public function getMyPlane():JsonResponse
    {

        $palnes=Plane::whereHas('airport' , function (Builder $query) {
            $query->where('user_id',auth()->id())->select('id','name','country_id','area_id','user_id');
        })->with('airport:id,name')->select('id','airport_id','name','number_of_seats','ticket_price')
                        ->get();

        return response()->json([
            'data'=>$palnes
         ],200);
    }
    public function store(Request $request):JsonResponse
    {

        $validator = Validator::make($request->all(), [
            'name'=> 'required|string|unique:planes,name',
            'airport_id'=>'required|numeric|exists:airports,id',
            'number_of_seats'=>'required|numeric|gt:10',
            'ticket_price'=> 'required|numeric|gt:0',
            'images'=> 'array',
            'images.*' => 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
          ]);

          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }

          $airport=Airport::where('id',$request->airport_id)->first();

          if(auth()->id() != $airport['user_id']){
                return response()->json([
                    'message'=>'You do not have the permission'
                ],200);
          }

          $plane=Plane::create([
            'name'=>$request->name,
            'airport_id'=>$request->airport_id,
            'number_of_seats'=>$request->number_of_seats,
            'ticket_price'=>$request->ticket_price
          ]);
          if($request->hasFile('images')){
            foreach ($request->file('images') as $imagefile){
                $images = new AirportImage;
                $images->plane_id= $plane->id;
                // $image_name=time() . '.' . $imagefile->getClientOriginalExtension();
                // $imagefile->move('AirportImage/',$image_name);
                // $images->image = "AirportImage/".$image_name;
                $images->image =ImageProcess::storeImage($imagefile,'AirportImage');
                $images->save();
            }
        }

        return response()->json([
            'date'=>Plane::with('airport:id,name')
                         ->select('id','airport_id','name','number_of_seats','ticket_price')
                         ->where('id',$plane->id)
                         ->get(),
        ],200);
    }

    public function addPlaneImage(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images'=> 'present|array|min:1',
            'images.*' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'plane_id'=>'required|numeric|exists:planes,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $plane=Plane::where('id',$request->plane_id)->first();
        $airport= Airport::findOrFail($plane->airport_id);
        if(auth()->id() != $airport->user_id)
        {
            return response()->json([
                'message'=>'You do not have the permission'
            ],403);
        }

        foreach ($request->file('images') as $imagefile){
            $images = new AirportImage;
            $images->plane_id= $plane->id;
            $images->image =ImageProcess::storeImage($imagefile,'AirportImage');
            $images->save();
        }

        return response()->json([
            'mesaage'=>'images added successfully'
        ],200);
    }

    public function updateExistPlaneImage(Request $request):JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'image_id'=>'required|numeric|exists:airport_images,id'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        $plane_image = AirportImage::where('id',$request->image_id)->first();
        $plane = Plane::where('id',$plane_image->plane_id)->first();
        $airport= Airport::findOrFail($plane->airport_id);
        if(auth()->id() != $airport->user_id)
        {
            return response()->json([
                'message'=>'You do not have the permission'
            ],200);
        }
        $plane_image->image=ImageProcess::updateImage($plane_image->image,$request->file('image'),'AirportImage');
        $plane_image->save();

        return response()->json([
            'mesaage'=>'images updated successfully'
        ],200);
    }

    public function update(Request $request,$id):JsonResponse
    {
        try{
            $palne= Plane::findOrFail($id);
            $airport=Airport::where('id',$palne->airport_id)->first();
            if(auth()->id() != $airport->user_id)
            {
                return response()->json([
                    'message'=>'You do not have the permission'
                ],200);
            }
        }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found',
            ],404);
        }
        $validator = Validator::make($request->all(), [
            'name'=> 'string',
            'number_of_seats'=> 'numeric|gt:10',
            'ticket_price'=> 'numeric|gt:0',
            'visible'=>'boolean'
          ]);

          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }

          $palne->name=$request->name;
          $palne->number_of_seats=$request->number_of_seats;
          $palne->ticket_price=$request->ticket_price;
          $palne->visible=$request->visible;
          $palne->save();

          return response()->json([
            'message'=> 'airport has been updated successfully',
            'data'=>Plane::with('airport:id,name')
                            ->select('id','name','user_id','area_id','country_id')
                            ->where('id',$palne->id)
                            ->get(),
          ],200);
    }

    public function addTrip(Request $request):JsonResponse
    {
        $date=Carbon::now()->format('Y-m-d');
        $validator = Validator::make($request->all(), [
            'plane_id'=> 'required|numeric|exists:planes,id',
            'airport_source_id'=>'required|numeric|exists:airports,id',
            'airport_destination_id'=>'required|numeric|exists:airports,id',
            'current_price'=> 'required|numeric|gt:0',
            'available_seats'=> 'required|numeric|gt:0',
            'flight_date' => "required|date|after_or_equal:$date",
            'landing_date' => 'required|date|after_or_equal:flight_date',
          ]);
          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }

          $trip=$this->planetriprepository->addTrip($request->all());

          if($trip){
              return response()->json([
                'message'=>'Trip added successfully',
                'date'=>$trip,
              ]);
          }

          return response()->json([
            'message'=>'Trip creation failed',
          ],400);
    }

    public function searchForPlaneTrip(Request $request):JsonResponse
    {
        $date=Carbon::now()->format('Y-m-d');
        $validator = Validator::make($request->all(), [
            'country_source_id'=> 'required|numeric|exists:countries,id',
            'country_destination_id'=> 'required|numeric|exists:countries,id',
            'flight_date' => "required|date|after_or_equal:$date",
        ]);
          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }
        $trips=$this->planetriprepository->getAllTripForCountry($request->all());

        return response()->json([
            'data'=>$trips,
        ],200);
    }

    public function getAllPlaneAdminTrip():JsonResponse
    {
        $trips=Airport::whereHas('trips')->with('trips')->where('user_id',auth()->id())->get();
        return response()->json([
            'data'=>$trips
        ],200);
    }
    
    public function getAllPlaneTrip(Request $request):JsonResponse
    {
        $trips=PlaneTrip::getTripDetails()->with('plane.airport:id,name')->get();
        return response()->json([
            'data'=>$trips
        ],200);

    }

    public function showPlaneTripDetails($id):JsonResponse
    {
        try{
            $plane_trip=PlaneTrip::findOrFail($id);
            // $palne= Plane::where('id',$plane_trip['plane_id'])->first();
            // $airport=Airport::where('id',$palne->airport_id)->first();
            // if(auth()->id() != $airport->user_id)
            // {
            //     return response()->json([
            //         'message'=>'You do not have the permission'
            //     ],200);
            // }
        }catch(\Exception $e){
            return response()->json([
                'message'=> 'Not found',
            ],404);
        }

        $trips=PlaneTrip::getTripDetails()->with('plane.airport:id,name')->where('id',$id)->first();
        return response()->json([
            'data'=>$trips,
        ],200);

    }
    public function book_trip_plane(Request $request){
                        $validatedData =Validator::make($request->all(),[
                            'book_id'=>'required|numeric|exists:bookings,id',
                            'plane_trip_id'=>'required|numeric|exists:plane_trips,id',
                    ]);
                    if( $validatedData->fails() ){
                        return response()->json([
                            'message'=> $validatedData->errors()->first(),
                        ],422);
                    }
                    try{
                    $book=BookPlane::create([
                        'book_id'=>$request->book_id,
                        'plane_trip_id'=>$request->plane_trip_id,
                    ]);
                }catch(Exception $e){
                    return response()->json([
                        'message'=>$e->getMessage(),
                    ],404);
                }
                return response()->json([
                    'data'=>$book
                ],200);


    }
}
