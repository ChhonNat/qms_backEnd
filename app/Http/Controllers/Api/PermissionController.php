<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function list_permission()
    {
        $isroles = Auth::user()->roles->pluck("name");
        // Check if the authenticated user has the 'admin' role
        if ($isroles[0] !== 'admin') {
            return response()->json([
                "success" => false,
                "message" => "Unauthorized access! Only admins can list permissions!."
            ]);
        }

        $user_permission = Permission::all();
        if ($user_permission->count() > 0) {
            return response()->json([
                "data" => $user_permission,
                "success" => true,
                "message" => "Success"
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "The record is empty!"
            ], 200);
        }
    }
}
