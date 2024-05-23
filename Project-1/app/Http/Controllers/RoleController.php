<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware("role:Super Admin", ["only"=> ["index","getAllPermission"]]);
    }


    public function getAllRoles(){
        $roles = Role::orderBy('id', 'asc')->where([['name','!=','Super Admin'],['name','!=','User'],['name','!=','Admin']])->
                                            get(['id','name']);
        return response()->json([
            "data"=> $roles
        ]);
    }

    public function getAllPermission(){
        $permissions = Permission::orderBy('id', 'asc')->get(['id','name']);
        return response()->json([
            "data"=> $permissions
        ]);
    }


    public function getAllPermissionForRole($id,Request $request){
        $role=Role::where('id',$id)->first();

        return response()->json([
            'data'=> $role->permissions()->get(['id','name']),
        ],200);
    }


    public function addRole(Request $request): JsonResponse
     {

        $validator = Validator::make($request->all(), [
            'name'=>'required|string|unique:roles',
            'permissions'=>'required|present|array|min:2',
            'permissions.*'=> 'required|numeric'
        ],
        [
            'min' => 'plz enter more than one permission for this role',
        ]);

        if( $validator->fails() ){
            return response()->json([
                'message'=> $validator->errors()->first(),
            ],422);
        }
        #############################
        $role = Role::create(['guard_name'=>'user','name' => $request->name]);
        #############################
        $permissions = Permission::whereIn('id', $request->permissions)->get(['name'])->toArray();

        $role->syncPermissions($permissions);

        return response()->json([
            'message'=>'role has been added',
            'role'=>Role::with(['permissions' => function ($query) {
                    $query->select('id', 'name');
                }])
                ->where('id', $role->id)
                ->get(['id','name'])
        ],200);

     }


    public function index()
    {   try {

            return response()->json([
                    'message'=>'Just Admin can use this'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message'=> '$e->getMessage()'
            ]);
       }

    }


    public function setRole(Request $request){

      //  $user=User::where('email',$request->email)->first();

        // $role=Role::where('name',$request->name)
        //             ->where('guard_name','user')
        //             ->first();

        $users=User::Role(['guard_name' => 'user'],$request->name)->get();

        // $user->assignRole($role);

        return response()->json([
            'message'=> 'done',
            'user-role'=>$users,
        ]);
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'message'=>'Just Super Admin can use this'
       ]);
    }

    public function index2()
    {
        return response()->json([
            'message'=>'everybody can use this'
       ]);
    }

    public function book(Request $req ){
            $role=Role::where('name','Product Manager')->first();
            $Roleofpermission=$role->permissions()->get()->pluck('name');

        return response()->json([
            //'message'=>$req->user()->permissions->pluck('name')
            'message'=>$req->user()->permissions->pluck('name'),
       ]);
    }
}
