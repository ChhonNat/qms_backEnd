<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbService;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    public function list_service()
    {
        $services = TbService::withoutTrashed()->get();

        if ($services->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No services found.",
            ], 200);
        }

        return response()->json([
            "success" => true,
            "message" => "Services retrieved successfully.",
            "data" => $services,
        ], 200);
    }

    public function store_service(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Some fields that insert is empty!"
            ], 200);
        }

        // $service = TbService::create();
        $service = TbService::create([
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ]);
        if ($service) {
            return response()->json([
                "success" => true,
                "message" => "the service create is succesfully",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "the data create is fail!",
            ], 200);
        }
    }
}
