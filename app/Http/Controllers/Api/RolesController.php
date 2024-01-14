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

class RolesController extends Controller
{
    // list Roles
    public function list_roles()
    {
        $isroles = Auth::user()->roles->pluck("name");
        // Check if the authenticated user has the 'admin' role
        if ($isroles[0] !== 'admin') {
            return Helper::sendError("Unauthorized access! Only admins can list roles.");
        }

        $roles = Role::all();
        if ($roles->count() > 0) {
            return response()->json([
                "data" => $roles,
                "success" => true,
                "message" => "Success"
            ]);
        } else {
            return Helper::sendError("The record is empty!");
        }
    }
}
