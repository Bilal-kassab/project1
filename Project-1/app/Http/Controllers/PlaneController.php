<?php

namespace App\Http\Controllers;

use App\Helpers\ImageProcess;
use App\Http\Requests\Plane\AddPlaneTripRequest;
use App\Http\Requests\Plane\StorePlaneRequest;
use App\Http\Requests\Plane\UpdatePlaneRequest;
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
        $this->middleware('role:Super Admin|Airport admin', ['only'=> ['getAllPlaneTrip']]);
    }

    public function getMyPlane():JsonResponse
    {
        $palnes=Plane::whereHas('airport' , function (Builder $query) {
            $query->where('user_id',auth()->id())->select('id','name','country_id','area_id','user_id');
        })->with('airport:id,name','images')->select('id','airport_id','name','number_of_seats','ticket_price')
                        ->get();

        return response()->json([
            'data'=>$palnes
            ],200);
    }
    public function store(StorePlaneRequest $request):JsonResponse
    {
          $airport=Airport::where('id',$request->airport_id)->first();

          if(auth()->id() != $airport['user_id']){
                return response()->json([
                    'message'=>trans('global.not-permission')
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
            'date'=>Plane::with(['airport:id,name','images:id,plane_id,image'])
                         ->select('id','airport_id','name','number_of_seats','ticket_price')
                         ->where('id',$plane->id)
                         ->first(),
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
                'message'=>trans('global.not-permission')
            ],403);
        }

        foreach ($request->file('images') as $imagefile){
            $images = new AirportImage;
            $images->plane_id= $plane->id;
            $images->image =ImageProcess::storeImage($imagefile,'AirportImage');
            $images->save();
        }

        return response()->json([
            'mesaage'=>trans('global.add')
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
                'message'=>trans('global.not-permission')
            ],200);
        }
        $plane_image->image=ImageProcess::updateImage($plane_image->image,$request->file('image'),'AirportImage');
        $plane_image->save();

        return response()->json([
            'mesaage'=>trans('global.update')
        ],200);
    }

    public function update(UpdatePlaneRequest $request,$id):JsonResponse
    {
        try{
            $palne= Plane::findOrFail($id);
            $airport=Airport::where('id',$palne->airport_id)->first();
            if(auth()->id() != $airport->user_id)
            {
                return response()->json([
                    'message'=>trans('global.not-permission')
                ],200);
            }
        }catch(\Exception $e){
            return response()->json([
                'message'=> trans('global.notfound'),
            ],404);
        }
          $palne->name=$request->name;
          $palne->number_of_seats=$request->number_of_seats;
          $palne->ticket_price=$request->ticket_price;
          $palne->visible=$request->visible;
          $palne->save();

          return response()->json([
            'message'=> trans('global.update'),
            'data'=>Plane::with('airport:id,name','images:id,plane_id,image')
                            ->select('id','name','airport_id','number_of_seats','ticket_price','visible')
                            ->where('id',$palne->id)
                            ->get(),
          ],200);
    }

    public function addTrip(AddPlaneTripRequest $request):JsonResponse
    {
          $trip=$this->planetriprepository->addTrip($request->all());
          if($trip){
              return response()->json([
                'message'=>trans('global.add'),
                'date'=>$trip,
              ]);
          }
          return response()->json([
            'message'=>trans('trip.trip-faild')
          ],400);
    }

    public function searchForPlaneTrip(Request $request):JsonResponse
    {
        $date=Carbon::now()->format('Y-m-d');
        $validator = Validator::make($request->all(), [
            'country_source_id'=> 'required|numeric|exists:countries,id',
            'country_destination_id'=> 'required|numeric|exists:countries,id',
            'flight_date' => "required|date|after_or_equal:$date",
            'type' => 'required|in:1,2|numeric',
            // 'end_date'  => 'required_if:type,2',
        ]);
          if($validator->fails()){
              return response()->json([
                  'message'=> $validator->errors()->first(),
              ],422);
          }
        // $returnTrip=$this->planetriprepository->getAllTripForCountry($request->all());
        $trips=$this->planetriprepository->getAllTripForCountry($request->all());
            if($request->type==1)
            {
            return response()->json([
                'data'=> $trips['going_trip'],
            ],200);
            }
        return response()->json([
            'data'=> $trips,
        ],200);
    }

    public function getAllPlaneAdminTrip():JsonResponse
    {
        $trips=Airport::whereHas('trips')
                      ->with('trips.plane:id,name','trips.plane.images:id,plane_id,image','trips.airport_source:id,name','trips.airport_destination:id,name','trips.country_source:id,name','trips.country_destination:id,name')
                      ->where('user_id',auth()->id())->get();
        return response()->json([
            'data'=>$trips
        ],200);
    }

    public function getAllPlaneTrip():JsonResponse
    {
        $trips=PlaneTrip::getTripDetails()->with('plane:id,airport_id,name','plane.airport:id,name')->get();
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
                'message'=> trans('global.notfound'),
            ],404);
        }

        $trips=PlaneTrip::getTripDetails()->with('plane:id,airport_id,name','plane.airport:id,name')->where('id',$id)->first();
        return response()->json([
            'data'=>$trips,
        ],200);

    }

    public function getAllTripsPlane($id):JsonResponse
    {
        try {
            $trips=Plane::with('tripss')->findOrFail($id);
        } catch (Exception $exception) {
            return response()->json([
                'message'=>trans('global.notfound')
            ],404);
        }
        return response()->json([
            'data'=>$trips
        ],200);
    }
}
