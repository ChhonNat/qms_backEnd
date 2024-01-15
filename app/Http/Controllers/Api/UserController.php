<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserRegisterResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\save;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // create user
    public function user_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "username" => "required",
            "phone_number" => "required",
            "email" => "required|email|unique:users,email",
            "password" => "required|min:4",
            "roles" => "required",
            "permissions" => "required|array",
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $emailErrorMessage = $errors->first('email');

            return response()->json([
                'success' => false,
                'message' => $emailErrorMessage ?? "Field ir required!"
            ], 200);
        }

        // request user login to check is admin or other
        $userLoged = Auth::user()->roles->pluck('name');
        if ($userLoged[0] === "admin") {
            // request id role to check
            $role_id = $request->roles;
            $role_data = Role::find($role_id);
            if (!$role_data) {
                return response()->json([
                    "success" => false,
                    "message" => "Don't have role in rocord!",
                    "data" => null
                ], 200);
            } else {
                $user_permission = $request->permissions;
                if ($role_data && $user_permission) {

                    $user = User::create([
                        'username' => $request->username,
                        'phone_number' => $request->phone_number,
                        'email' => $request->email,
                        'password' => bcrypt($request->password),
                    ]);

                    // Assign role and permission
                    $user->assignRole($role_data->name);
                    $user->givePermissionTo($user_permission);
                }
                return response()->json([
                    "success" => true,
                    "message" => "the data create is succesfully",
                ], 200);
            }
        } else {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized access! Only admins can create user.",
                "data" => null
            ]);
        }
    }


    // update user
    public function update_user(Request $request, $id)
    {
        $userLoggedRoles = Auth::user()->roles->pluck('name');

        if ($userLoggedRoles->contains("admin")) {
            // check validate all fields from request data
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|min:1',
                'phone_number' => 'required|string|min:9|max:10',
                'email' => 'required|email|min:1',
                // 'password' => 'required|string|min:6',
                // 'password_confirmed' => 'required|same:password',
                'roles' => 'required|integer|min:1', // Assuming roles should be an integer
                'permissions' => 'required|array|min:1', // Assuming permissions should be an array with at least one element
                'status' => 'required|integer|in:0,1', // Assuming status should be either 0 or 1
            ]);

            if ($validator->fails()) {
                return response()->json([
                    "success" => false,
                    "message" => "Some fields that insert is empty!"
                ], 200);
            }

            // retrieve user that match id
            $user = User::find($id);
            // check user in record database
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found!",
                    "data" => null
                ], 200);
            }

            // Update user details including the 'status' field
            $user->update([
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                // 'password' => bcrypt($request->password),
                'status' => $request->status,
            ]);
            // request id role
            $role_id = $request->roles;
            // check id in role table
            $role_data = Role::find($role_id);
            // update role that match name
            if ($role_data) {
                $user->syncRoles([$role_data->name]);
            }
            // request all permissions as array
            $user_permissions = $request->permissions;
            // udpate table permissions
            if ($user_permissions) {
                $user->syncPermissions($user_permissions);
            }

            event(new NewMessage($user));
            return response()->json([
                "success" => true,
                "message" => "User data updated successfully",
            ], 200);

        } else {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized access! Only admins can update user.",
                "data" => null
            ]);
        }
    }

    // list user
    public function list_user()
    {
        $isroles = Auth::user()->roles->pluck("name");
        // Check if the authenticated user has the 'admin' role
        if ($isroles[0] !== 'admin') {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized access! Only admins can list users !",
                "data" => null
            ], 200);
        } else {
            $users = User::whereNull('deleted_at')->get();
            $dataRes = [];

            foreach ($users as $user) {
                $userData = [
                    "id" => $user->id,
                    "username" => $user->username,
                    "phone_number" => $user->phone_number,
                    "email" => $user->email,
                    "status" => $user->status ? "Active" : "Inactive",
                    "rolesId" => $user->roles->pluck("id")->implode(', '),
                    "roles" => $user->roles->pluck("name")->implode(', '),
                    "permissions" => $user->permissions->pluck("name")->implode(', '),
                    "created_at" => $user->created_at,
                ];

                $dataRes[] = $userData;
            }
            if ($user->count() > 0) {
                return response()->json([
                    "success" => true,
                    "message" => "successfully get data",
                    "data" => $dataRes
                ], 200);
            } else {
                return Helper::sendError("The record is empty!");
            }
        }
    }

    // delelte user
    public function delete_user($id)
    {
        $userLoggedRoles = Auth::user()->roles->pluck('name');
        // check on Auth is contain user name admin or not
        if ($userLoggedRoles->contains("admin")) {
            // retrieve user that matches the given ID
            $user = User::find($id);

            // check if the user exists in the database
            if (!$user) {
                return response()->json([
                    "success" => false,
                    "message" => "User not found!",
                    "data" => null
                ], 200);
            }

            // Perform soft delete by updating the 'deleted_at' column
            $user->update([
                'deleted_at' => now() // assuming 'deleted_at' is a timestamp field
            ]);

            return response()->json([
                "success" => true,
                "message" => "User deleted successfully by soft delete",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized access! Only admins can delete user.",
            ]);
        }
    }
}
