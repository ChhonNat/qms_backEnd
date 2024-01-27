<?php

namespace App\Http\Controllers\Api;

use App\Events\IsTicketed;
use App\Http\Controllers\Controller;
use App\Models\TbScreenTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ScreenTicketController extends Controller
{
    public function screen_ticket_store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "service_id" => "required|integer",
            "status" => "required|integer|in:0,1"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Some fields that insert is empty!"
            ], 200);
        };

        // $dataScreenTicket = TbScreenTicket::create([
        //     "service_id" => $request->service_id,
        //     "status" => $request->status
        // ]);

        $dataScreenTicket = DB::select('CALL storeDataScreenTicket(?, ?)', [$request->service_id, $request->status]);

        // get message from store procedure
        $message = $dataScreenTicket[0]->message;

        if ($message) {
            return response()->json([
                "success" => true,
                "message" => $message,
            ], 200);
        }
    }

    public function screen_ticket_list()
    {
        $dataList = TbScreenTicket::all();

        if ($dataList->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No data found.",
                "data" => null
            ], 200);
        }

        $dataRes = [];

        foreach ($dataList as $record) {
            $recordData = [
                "id" => $record->id,
                "service_id" => $record->service_id,
                "status" => $record->status ? "Active" : "Inactive",
                "created_at" => $record->created_at,
                "updated_at" => $record->updated_at,
            ];

            $dataRes[] = $recordData;
        }

        return response()->json([
            "success" => true,
            "message" => "data retrieved successfully.",
            "data" => $dataRes,
        ], 200);
    }
}
