<?php

namespace App\Http\Controllers\Api;

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
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // User Login
    public function login(LoginRequest $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return Helper::sendError('Email or Password is incorrect!');
        }
        $listUser = User::where('email', $request->email)->first();
        $dataRes = [
            "user_id" => $listUser->id,
            "username" => $listUser->username,
            "phone_number" => $listUser->phone_number,
            "email" => $listUser->email,
            "status" => $listUser->status,
            "token" => $listUser->createToken("Token")->plainTextToken,
            "roles" => $listUser->roles->pluck("name"),
            "permissions" => $listUser->permissions->pluck("name"),
        ];
        // return new UserResource($listUser);
        return response()->json([
            "data" => $dataRes,
            "success" => true,
            "message" => "Success"
        ], 200);
    }

    // User Register
    public function register(RegisterRequest $request)
    {
        // request role and permision
        $user_role = $request->roles;
        $user_permission = $request->permissions;
        if ($user_role && $user_permission) {

            $user = User::create([
                'username' => $request->username,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'password' => bcrypt($request->password),
            ]);

            // Assign role and permission
            $user->assignRole($user_role);
            $user->givePermissionTo($user_permission);
        }

        // send response
        return response()->json([
            "success" => true,
            "message" => "the data create is succesfully",
        ], 200);
    }

    public function changePass(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_pass' => 'required',
                'new_pass' => 'required|min:4',
                'password_confirmed' => 'required|same:new_pass'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                    'status' => false
                ], 422);
            }

            // $user = auth()->user();
            $user = User::find(auth()->id());

            if (!$user) {
                return response()->json([
                    'message' => 'User not found',
                    'status' => false
                ], 404);
            }

            // Option Remember Me
            // Check if the provided token matches the user's current remember_token
            // if (!Hash::check($request->token, $user->remember_token)) {
            //     return response()->json([
            //         'message' => 'Token mismatch. Unauthorized action.',
            //         'status' => false
            //     ], 401);
            // }

            if (Hash::check($request->current_pass, $user->password)) {
                $user->password = Hash::make($request->new_pass);
                // $user->remember_token = Str::random(60);
                $user->save();

                return response()->json([
                    'message' => 'Password changed successfully',
                    'status' => true
                ], 200);
            } else {
                return response()->json([
                    'message' => 'The current password is incorrect!',
                    'status' => false
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while changing the password.',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();
            return response()->json([
                'message' => 'Logout successful',
                'status' => true
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
}
