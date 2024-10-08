<?php

namespace App\Http\Controllers;

use App\Events\PushWebNotification;
use App\Jobs\SendEmail;
use App\Mail\TestMail;
use App\Mail\VerfiyEmail;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\ConfirmCode;
use App\Models\Country;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super Admin', ['only' => ['deleteAccountBySA']]);
    }
    public function register(Request $request)
    {

        $registerUserData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'position'=>'numeric|exists:countries,id',
            'phone_number'=>'regex:/[0-9]{10}/|unique:users',
            'fcm_token'=>'string'
        ]);
        $user=new User;
        if($request->hasFile('image')){

            $image = $request->file('image');
            $image_name=time() . '.' . $image->getClientOriginalExtension();
            $image->move('ProfileImage/',$image_name);
            $user->image="ProfileImage/".$image_name;
        }

        $user->name=$registerUserData['name'];
        $user->email=$registerUserData['email'];
        $user->is_approved=1;
        $user->password= Hash::make($registerUserData['password']);
        $user->phone_number=$registerUserData['phone_number'] ?? null;
        $user->position=$registerUserData['position'] ?? null;
        $user->fcm_token=$registerUserData['fcm_token']??null;
        ###################
        $user->assignRole('User');
        $user->givePermissionTo('unbanned');
        #######################
        ########################لا تنسى تشيلها
        $user->email_verified_at=Carbon::now()->format('Y-m-d');

        $user->save();

        $bank=Bank::create([
            'email'=>$user->email,
        ]);

        $token = $user->createToken('token')->plainTextToken;

        $registerUserData['code']=mt_rand(100000,999999);

        while(ConfirmCode::firstWhere('code', $registerUserData['code'])){
            $registerUserData['code']=mt_rand(100000,999999);
        }
        $confdetails=ConfirmCode::create([
            'code'=>$registerUserData['code'],
            'email'=>$registerUserData['email'],
        ]);


        // Mail::to($registerUserData['email'])->send(new TestMail($user,$confdetails));
        dispatch(new SendEmail($user,$confdetails));
        $all=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>Country::where('id',$user->position)->first(),
            'token'=> $token,
            'code'=>$registerUserData['code'],
        ];
        return response()->json([
            // 'data'=>[
            //         'user'=>$user,
            //          'code'=>$registerUserData['code']
            // ],
            // 'token'=>$token
            'data'=>$all
        ],200);
    }

    public function login(Request $request)
    {
        $loginUserData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8',
            'fcm_token'=>'string'
        ]);

        $user = User::where('email',$loginUserData['email'])->first();

        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            return response()->json([
                'message' => trans('auth.failed')
            ],401);
        }
        if(!$user['email_verified_at']){
            return response()->json([
                'message'=>trans('global.not-authorization')
            ],200);
        }
        $token = $user->createToken('token')->plainTextToken;
        $user->fcm_token=$loginUserData['fcm_token']??null;
        $user->save();
        return response()->json([
            'message'=> 'login done',
            'token' => $token,
        ],200);
    }

    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();

        return response()->json([
        'message' => 'Successfully logged out'
        ],200);
    }

    public function confirmCode(Request $request){
        $registerUserData = $request->validate([
            'email'=>'required|string|email',
            'code'=>'required|min:6'
        ]);
        //$registerUserData['email']
        $myCode=ConfirmCode::firstWhere('email', $registerUserData['email']);

        if($registerUserData['code'] !=  $myCode['code'] ){
            return response()->json([
                'message'=> 'worng code'
            ],200);
        }
        $user=User::where('email',$registerUserData['email'])->first();

        $user->email_verified_at=Carbon::now();
        $user->save();

        return response()->json([
            'message'=> 'welcome to here'
        ],200);
    }

    public function forgetPassword_SendEmail(Request $request){
        $registerUserData = $request->validate([
            'email'=>'required|string|email',
        ]);

        $dataUser=User::firstWhere('email',$registerUserData['email']);

        if(!$dataUser){
            return response()->json([
                'message'=> 'there is no email like this'
            ],200);
        }

        ConfirmCode::firstWhere('email',$registerUserData['email'])->delete();

        $registerUserData['code']=mt_rand(100000,999999);

        while(ConfirmCode::firstWhere('code', $registerUserData['code'])){
            $registerUserData['code']=mt_rand(100000,999999);
        }

        $confdetails=ConfirmCode::create([
            'code'=>$registerUserData['code'],
            'email'=>$registerUserData['email'],
        ]);

       // Mail::to($registerUserData['email'])->send(new ConfirmationEmail($confdetails));

        return response()->json([
            'meseage'=> 'we have sent to ur email a code please check it',
            'code'=> $registerUserData['code']
        ],200);

    }

    public function forgetPassword_SetPassword(Request $request){

        $userinfo=$request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8|confirmed'
        ]);

       $user=User::firstWhere('email',$userinfo['email']);

        $user->update([
            'password'=>$userinfo['password']
        ]);

        return response()->json([
            'message'=>'password updated'
        ],200);
    }

    public function profile(){

        $user=auth()->user();
        $position=Country::where('id',$user->position)->first();

        $data=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'point'=>$user->point,
            'fcm_token'=>$user->fcm_token,
            'position'=>$position,
        ];

        return response()->json([
            'data'=>$data,
        ],200);
    }

    public function changeProfilePhoto(Request $request){

        $request->validate([
            'image'=> 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $user=User::find(auth()->user()->id);


        if(File::exists($user->image))
        {
            File::delete($user->image);
        }

        $image = $request->file('image');
        $image_name=time() . '.' . $image->getClientOriginalExtension();
        $image->move('ProfileImage/',$image_name);
        $user->image="ProfileImage/".$image_name;
        $user->save();

        // $data=[
        //     'id'=>$user->id,
        //     'name'=>$user->name,
        //     'email'=> $user->email,
        //     'image'=> $user->image,
        // ];
        $position=Country::where('id',$user->position)->first();
        $data=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>$position,
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            //'data'=>$user->get(['id','name','email','image'])
            'data'=>$data,
        ],200);

    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'string',
            'position'=>'numeric|exists:countries,id',
            'phone_number'=>'regex:/[0-9]{10}/|unique:users'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        $user=User::findOrFail(auth()->user()->id);

        $user->phone_number=$request['phone_number'] ??$user['phone_number'];
        $user->position=$request['position'] ?? $user['position'];
        $user->name=$request['name'] ?? $user['name'];
        $user->save();
        $position=Country::where('id',$user->position)->first();
        $data=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>$position,
        ];

        return response()->json([
            'message'=> 'updated successfully',
            'data'=>$data
        ],200);
    }

    public function deleteProfilePhoto(){

        $user=User::find(auth()->user()->id);

        if($user->image==null){
            return response()->json([
                'message'=> 'u do not has a profile image to delete'
            ],200);
        }

        if(File::exists($user->image))
        {
            File::delete($user->image);
        }
        $user->image=null;
        $user->save();

        $data=[
            'id'=>$user->id,
            'name'=>$user->name,
            'email'=> $user->email,
            'image'=> $user->image,
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            //'data'=>$user->get(['id','name','email','image'])
            'data'=>$data,
        ],200);

    }

    public function paymentInof()
    {
        try{
            $info=Bank::where('email',auth()->user()->email)->first();
            $data=[
                'id'=>$info['id'],
                'email'=>$info['email'],
                'money'=>$info['money'],
                'payments'=>$info['payments'],
                'point'=>User::where('id',auth()->id())->first()['point'],
                'created_at'=>$info['created_at'],
                'updated_at'=>$info['updated_at'],
            ];
             return response()->json([
                'data'=>$data
             ],200);
        }catch(Exception $ex){
            return response()->json([
                'message'=>$ex->getMessage()
            ]);
        }
    }

    public function deleteAccount()
    {
        $rooms=BookingRoom::where('user_id',auth()->id())->whereRelation('book','type','static')->get();
        foreach($rooms as $room){
            $room['user_id']=null;
            $room->save();
        }
       $user=User::where('id',auth()->id())->first();
       Bank::where('email',$user->email)->delete();
       $user->delete();
        return response()->json([
            // 'message'=>$book
            'message'=>trans('global.delete')
        ],200);
    }
    public function deleteAccountBySA($id)
    {
        $rooms=BookingRoom::where('user_id',$id)->whereRelation('book','type','static')->get();
        foreach($rooms as $room){
            $room['user_id']=null;
            $room->save();
        }
       $user=User::where('id',$id)->delete();

        return response()->json([
            // 'message'=>$book
            'message'=>trans('global.delete')
        ],200);
    }

    public function chargeAccount(Request $request)
    {
        $request->validate([
            'money'=>'required|numeric',
            'email'=>'required|email'
        ]);

        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        $response=$stripe->checkout->sessions->create([
        'line_items' => [
            [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                            'name' =>'Charge Account',
                    ],
                    //trip price
                    'unit_amount' =>$request->money*100 ,
                    // 'unit_amount' =>$request['money']*100 ,
                ],
                'quantity' => 1,
            ]
        ],
        'mode' => 'payment',
        // 'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
        // 'success_url' => self::success($request),
        'success_url' =>route('success-charge',[
            'email' => $request->email,
            'money' => $request->money,
        ]),
        'cancel_url' => route('cancel'),
        ]);
        if(isset($response->id)&& $response->id != ''){
            return response()->json([
                'link'=>$response->url
            ],200);
        }else{
            return redirect()->route('cancel');
        }
    }
    public function success(Request $request)
    {
            // dd($request->email);
            $bank=Bank::where('email',$request->email)->first();
            $bank->money+=$request->money;
            $bank->save();
            $user=User::where('email',$request->email)->get();
            $message=[
                'title'=>'Account Recharge',
                'body'=>"Your account has been credited with ". $request->money ."$ Enjoy!",
            ];

        event(new PushWebNotification($user,$message));
            return response()->json([
                'message'=>'Enjoy Trip'
            ],200);
    }

    public function cancel()
    {
        return response()->json([
            'message'=>'failed plz try again'
        ],422);
    }
}
