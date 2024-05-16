<?php

namespace App\Http\Controllers;

use App\Models\ConfirmCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function register(Request $request)
    {

        $registerUserData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users',
            'password'=>'required|min:8|confirmed',
            'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'position'=>'numeric|exists:countries,id',
            'phone_number'=>'regex:/[0-9]{10}/|unique:users'
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
        $user->password= Hash::make($registerUserData['password']);
        $user->phone_number=$registerUserData['phone_number'] ?? null;
        $user->position=$registerUserData['position'] ?? null;

        ###################
        $user->assignRole('User');
        #######################

        $user->save();
        $token = $user->createToken('token')->plainTextToken;

        $registerUserData['code']=mt_rand(100000,999999);

        while(ConfirmCode::firstWhere('code', $registerUserData['code'])){
            $registerUserData['code']=mt_rand(100000,999999);
        }
        $confdetails=ConfirmCode::create([
            'code'=>$registerUserData['code'],
            'email'=>$registerUserData['email'],
        ]);

        //Mail::to($registerUserData['email'])->send(new ConfirmationEmail($confdeatiles));
        $all=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>$user->position,
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
            'password'=>'required|min:8'
        ]);

        $user = User::where('email',$loginUserData['email'])->first();

        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $token = $user->createToken('token')->plainTextToken;
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

        $data=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>$user->position,
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

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'=>'required|string',
            'position'=>'numeric|exists:countries,id',
            'phone_number'=>'regex:/[0-9]{10}/|unique:users'
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }

        $user=User::findOrFail(auth()->user()->id);

        $user->phone_number=$request['phone_number'] ??null;
        $user->position=$request['position'] ?? null;
        $user->name=$request['name'] ?? null;
        $user->save();

        $data=[
            'id'=> $user->id,
            'name'=> $user->name,
            'email'=> $user->email,
            'phone_number'=>$user->phone_number,
            'image'=> $user->image,
            'position'=>$user->position,
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
}
