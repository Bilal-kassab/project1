<?php

namespace App\Http\Controllers;

use App\Events\PushWebNotification;
use App\Http\Requests\Admin\BanRequest;
use App\Http\Requests\Admin\SearchByNameRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Airport;
use App\Models\Country;
use App\Models\Hotel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Super Admin', ['only'=> ['searchByName','approveUser','getAllAdmin','adminsRequests','ban']]);
        //$this->middleware('', [''=> ['','']]);

    }
    public function addAdmin(RegisterRequest $request){

        // $registerAdminData = $request->validate([
        //     'name'=>'required|string',
        //     'email'=>'required|string|email|unique:users',
        //     'password'=>'required|min:8|confirmed',
        //     'role_id'=>'required|numeric',
        //     'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        //     'position'=>'numeric|exists:countries,id',
        //     'phone_number'=>'regex:/[0-9]{10}/|unique:users'
        // ]);
        $registerAdminData=$request->all();
        try{
            $role=Role::where('id',$request->role_id)->first();
        }catch(\Exception $exception){
            return response()->json([
                'message'=>$exception->getMessage()
            ],404);
        }
        $admin =new User;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $image_name=time() . '.' . $image->getClientOriginalExtension();
            $admin->image="ProfileImage/".$image_name;
        }

        $admin->name=$registerAdminData['name'];
        $admin->email=$registerAdminData['email'];
        $admin->password= Hash::make($registerAdminData['password']);
        $admin->phone_number=$registerAdminData['phone_number'] ?? null;
        $admin->position=$registerAdminData['position'] ?? null;
        $admin->fcm_token=$registerAdminData['fcm_token']??null;
        $admin->assignRole($role->name);
        // if($request->has('role')){
        //     $admin->assignRole($request->role);
        // }
        $admin->save();
        $admin->givePermissionTo('unbanned');

        $token = $admin->createToken('token')->plainTextToken;
        $data=[
            'id'=> $admin->id,
            'name'=> $admin->name,
            'email'=> $admin->email,
            'phone_number'=>$admin->phone_number,
            'image'=> $admin->image,
            'position'=>Country::where('id',$admin->position)->first(),
            'role'=>$role->name,
            'is_approved'=>$admin->is_approved,
            //'token'=> $token,
        ];
        $user=User::where('id',1)->get();
            $message=[
                'title'=>'Admin Request',
                // 'body'=>auth()->user()->name." has registered for a trip",
                'body'=>"The admin ".$admin->name." has registered on the app and is awaiting approval.",
            ];

            event(new PushWebNotification($user,$message));
        return response()->json([
             //'data'=>$admin,
             //'token'=>$token
            'data'=>User::where('id',$admin->id)->with('position:id,name')->first(),
        ],200);
    }

    public function login(LoginRequest $request)
    {
        $loginAdminData =$request->all();


        $admin = User::where('email',$loginAdminData['email'])->with('position:id,name')->first();

        if(!$admin || !Hash::check($loginAdminData['password'],$admin->password)){
            return response()->json([
                'message' => trans('auth.failed')
            ],422);
        }
        $token = $admin->createToken('token')->plainTextToken;
        $admin->fcm_token=$loginAdminData['fcm_token']??null;
        $admin->save();
        $object=null;
        $object=Hotel::where('user_id',$admin['id'])->first();
        if($object==null){
            $object=Airport::where('user_id',$admin['id'])->first();
        }
        $role=$admin->roles()->pluck('name');
        if($role[0]=="Super Admin"  || $role[0]=="Trip manger"){
            $object['name']='Owner';
        }
        return response()->json([
            'message'=> trans('auth.login'),
            'data'=>$admin,
            'role'=>$role,
            'token' => $token,
            'object'=>$object['name']??null
        ],200);
    }

    public function logout(Request $request)
    {

         $request->user()->tokens()->delete();
        return response()->json([
            'message' => trans('auth.logout')
        ],200);
    }

    public function profile()
    {

        $admin=auth()->user();
        $position=Country::where('id',$admin->position)->first();

        $data=[
            'id'=> $admin->id,
            'name'=> $admin->name,
            'email'=> $admin->email,
            'phone_number'=>$admin->phone_number,
            'image'=> $admin->image,
            'position'=>$position,
            'role'=> $admin->roles->pluck('name'),
        ];

        return response()->json([
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

        $admin=User::findOrFail(auth()->user()->id);

        $admin->phone_number=$request['phone_number'] ??$admin['phone_number'];
        $admin->position=$request['position'] ?? $admin['position'];
        $admin->name=$request['name'] ?? $admin['name'];
        $admin->save();
        $position=Country::where('id',$admin->position)->first();
        $data=[
            'id'=> $admin->id,
            'name'=> $admin->name,
            'email'=> $admin->email,
            'phone_number'=>$admin->phone_number,
            'image'=> $admin->image,
            'position'=>$position,
            'role'=> $admin->roles->pluck('name'),
        ];

        return response()->json([
            'message'=> trans('auth.update-profile'),
            'data'=>$data
        ],200);
    }

    public function changeProfilePhoto(Request $request)
    {
        $data=$request->validate([
            'image'=> 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        $admin=User::find(auth()->user()->id);
        if(File::exists($admin->image))
        {
            File::delete($admin->image);
        }
        $image = $request->file('image');
        $image_name=time() . '.' . $image->getClientOriginalExtension();
        $image->move('ProfileImage/',$image_name);
        $admin->image="ProfileImage/".$image_name;
        $admin->save();
        // $data=[
        //     'id'=>$admin->id,
        //     'name'=>$admin->name,
        //     'email'=> $admin->email,
        //     'image'=> $admin->image,
        // ];
        $position=Country::where('id',$admin->position)->first();
        $data=[
            'id'=> $admin->id,
            'name'=> $admin->name,
            'email'=> $admin->email,
            'phone_number'=>$admin->phone_number,
            'image'=> $admin->image,
            'position'=>$position,
        ];
        return response()->json([
            'message'=>'photo updated successfully',
            'data'=>$data
        ],200);

    }

    public function deleteProfilePhoto()
    {

        $admin=User::find(auth()->user()->id);

        if($admin->image==null){
            return response()->json([
                'message'=> trans('auth.delete-profile-photo-does-not-exist')
            ],200);
        }

        if(File::exists($admin->image))
        {
            File::delete($admin->image);
        }
        $admin->image=null;
        $admin->save();

        $data=[
            'id'=>$admin->id,
            'name'=>$admin->name,
            'email'=> $admin->email,
            'image'=> $admin->image,
        ];
        return response()->json([
            'message'=>trans('auth.delete-profile-photo'),
            //'data'=>$user->get(['id','name','email','image'])
            //'data'=>$data,
        ],200);

    }


    public function changeName(Request $request)
    {

        $data=$request->validate([
            'name'=> 'required|string'
        ]);
        $user=User::find(auth()->user()->id);
        $user->name=$data['name'];
        $user->save();

        return response()->json([
            'message'=> 'name updated successfully'
        ],200);

    }

    // public function getAllAdmin()
    // {

    //     $user=User::Role(['guard_name'=>'user','Admin'])->get(['id','name','email','image']);
    //     return response()->json([
    //         'data'=> $user,
    //     ],200);

    // }

    public function getAdmin($id)
    {

        $user=User::where('id',$id)->first();
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
            'data'=> $data,
        ],200);

    }

    public function getAdmisForRole($id)
    {

       $role=Role::query()->orderBy('id', 'asc')->where('id',$id)->first();
       $admins=User::query()->Role($role->name)->select('id','name','email','phone_number','image','position','is_approved')
                    ->with('position')
                    ->get();

       return response()->json([
        'data'=> $admins,
       ],200);

    }

    public function approveUser(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'user_id' => 'required|numeric|exists:users,id',
            'status'=>'required|in:0,1'
        ]);
        if( $validatedData->fails() ){
            return response()->json([
                'message'=> $validatedData->errors()->first(),
            ],422);
        }
        $user=User::where('id',$request->user_id)->first();
        $role=$user->roles[0]['name'];

        $userNoti=User::where('id',$request->user_id)->get();
        if($request->status){
            $user->is_approved = true;
            $user->save();
            $message=[
                'title'=>'Request Approval',
                'body'=>"Your request to apply as a ". $role ." within the app has been accepted.",
            ];
            event(new PushWebNotification($userNoti,$message));

            return response()->json([
                'message'=>trans('auth.approve-admin'),
            ],200);
        }else{
            $message=[
                'title'=>'Request Rejection',
                'body'=>"Sorry, your request has been rejected.",
            ];
            event(new PushWebNotification($userNoti,$message));
            $user->delete();
            return response()->json([
                'message'=>trans('auth.reject-admin'),
            ],200);
        }



        // Notification::send($user, new UserApprovedNotification());

    }

    public function filter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'by_name'=>'in:desc,asc',
            'most_recent'=>'boolean',
            'role_id'=>'numeric|exists:roles,id',
            'user_type'=>'|in:banned,unbanned',
        ]);

        if($validator->fails()){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        $role=Role::where('id',$request['role_id'])->first();
        $permission=Permission::where('name',$request['user_type'])->first();
        $users=User::query()
                ->when($request['by_name'] == 'asc',function($q) {
                    return $q->orderBy('name','asc');
                })
                ->when($request['by_name'] == 'desc',function($q) {
                    return $q->orderBy('name','desc');
                })
                ->when($request['most_recent'] == true ,function($q) {
                    return $q->orderBy('created_at','desc');
                })
                ->when($role,function($q) use ($role){
                    return $q->Role($role->name);
                })
                ->when($permission,function($q) use ($permission){
                    return $q->Permission($permission->name);
                })
                ->select('id','name','email','phone_number','image','position','is_approved')
                ->with('roles:id,name','position','permissions:id,name')
                ->whereRelation('roles','name','!=','Super Admin')->get();

        return response()->json([
            'data'=>$users,
        ],200);
    }

    public function adminsRequests()
    {
        // $admins=User::query()->where();
        $user=User::whereHas("roles", function($q) {
            $q->whereIn("name", ["Trip manger","Hotel admin",'Airport admin']);
            })->where('is_approved',false)->with('position')->get();

            return response()->json([
                'data'=>$user
            ],200);
    }

    public function searchByName(SearchByNameRequest $request)
    {
        $users=User::where('name','like','%'.$request->name.'%')
                    ->select('id','name','email','phone_number','image','position','is_approved')
                    ->whereRelation('roles','name','!=','Super Admin')
                    ->with('roles:id,name','position')
                    ->get();
          return response()->json([
            'data'=>$users
        ],200);

    }

    public function ban(BanRequest $request)
    {
        $user=User::where('id',$request['user_id'])->first();

        if($user->hasPermissionTo('unbanned')){
            $user->revokePermissionTo('unbanned');
            $user->givePermissionTo('banned');
            return response()->json([
                'message'=>'The account has been banned'
            ],200);
        }
        else{
            $user->revokePermissionTo('banned');
            $user->givePermissionTo('unbanned');
            return response()->json([
                'message'=>'The account has been unblocked.'
            ],200);
        }

    }

}
