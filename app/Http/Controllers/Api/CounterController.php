<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CounterController extends Controller
{
    public function list_counter()
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
            $users = User::where('username', '!=', 'admin')
                ->whereNull('deleted_at')
                ->get();
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
                return response()->json([
                    "success" => false,
                    "message" => "The record is empty",
                    "data" => null
                ], 200);
            }
        }
    }
}
