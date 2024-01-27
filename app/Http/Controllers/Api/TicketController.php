<?php

namespace App\Http\Controllers\Api;

use App\Events\IsTicketed;
use App\Http\Controllers\Controller;
use App\Models\TbTicket as ModelsTbTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use TbTicket;

class TicketController extends Controller
{
    public function list_ticket()
    {
        $Ticket = ModelsTbTicket::all();

        if ($Ticket->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "No Ticket found.",
                "data" => null
            ], 200);
        }

        $dataRes = [];

        foreach ($Ticket as $ticket) {
            $ticketData = [
                "id" => $ticket->id,
                "service_id" => $ticket->service_id,
                "ticket_no" => $ticket->ticket_no,
                "is_called" => $ticket->is_called,
                "created_at" => $ticket->created_at,
                "updated_at" => $ticket->updated_at,
            ];

            $dataRes[] = $ticketData;
        }

        return response()->json([
            "success" => true,
            "message" => "Ticket retrieved successfully.",
            "data" => $dataRes,
        ], 200);
    }

    public function store_ticket(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "Service field that insert is empty!"
            ], 200);
        }

        // call store procedure
        $sql = "CALL IsTicketed('$request->service_id', @last_ticket_no, @waitingNumber)";

        $result = DB::select($sql);

        // Retrieve the output parameters after the stored procedure call
        $outputParameters = DB::select('SELECT @last_ticket_no AS last_ticket_no, @waitingNumber AS waitingNumber')[0];

        // Access the output parameters
        $lastTicketNo = $outputParameters->last_ticket_no;
        $waitingNumber = $outputParameters->waitingNumber;

        if (isset($result)) {
            $data=[
                "waintingNum"=> $waitingNumber,
                "lastTicketed"=> $lastTicketNo,
                "message" => "respose successfully"
            ];
            // send is ticketed number to another client that subcripted
            event(new IsTicketed($data));
            return response()->json([
                "success" => true,
                "message" => "The ticket is created successfully",
                "data" => $lastTicketNo
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Failed to create ticket",
            ], 200);
        }
    }

    public function update_ticket(Request $request, $id)
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

        $service = ModelsTbTicket::find($id);

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
        $service['name'] = $request->name;
        $service['description'] = $request->description;
        $service['status'] = $request->status;
        $service->update();

        return response()->json([
            "success" => true,
            "message" => "Service updated successfully.",
        ], 200);
    }
}
