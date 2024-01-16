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
                "data" => null
            ], 200);
        }

        $dataRes = [];

        foreach ($services as $service) {
            $serviceData = [
                "id" => $service->id,
                "name" => $service->name,
                "description" => $service->description,
                "status" => $service->status ? "Active" : "Inactive",
                "created_at" => $service->created_at,
                "updated_at" => $service->updated_at,
            ];

            $dataRes[] = $serviceData;
        }

        return response()->json([
            "success" => true,
            "message" => "Services retrieved successfully.",
            "data" => $dataRes,
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

    public function update_service(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'status' => 'required|integer|in:0,1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Some fields are empty or contain invalid data.",
            ], 200);
        }

        $service = TbService::find($id);

        if (!$service) {
            return response()->json([
                "success" => false,
                "message" => "Service not found.",
            ], 200);
        }

        // Update the service attributes
        // $service->update([
        //     'name' => $request->name,
        //     'description' => $request->description,
        //     'status' => $request->status
        // ]);
        $service['name']=$request->name;
        $service['description']=$request->description;
        $service['status']=$request->status;
        $service->update();

        return response()->json([
            "success" => true,
            "message" => "Service updated successfully.",
        ], 200);
    }

    public function delete_service($id)
    {
        $service = TbService::find($id);
        $service->delete();
        return response()->json([
            "success" => true,
            "message" => "Service deleted successfully by soft delete",
        ], 200);
    }
}
