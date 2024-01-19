<?php

namespace App\Http\Controllers\Api;

use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\TbQueue;
use App\Models\TbTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    // counter called
    public function is_called(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'counter_id' => 'required|integer',
            'q_name' => 'nullable',
            'noted' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                "message" => "counter_id fields that insert is empty!"
            ], 200);
        }

        // list all tickets
        $ticketData = TbTicket::where('is_called', '!=', 1)->first();
        if(!isset($ticketData)){
            return response()->json([
                "success" => false,
                "message" => "No Ticked!",
            ], 200);
        }
        // define variable to insert PROCEDURE
        $TmpParamProc = [
            'ticket_id' => $ticketData['id'],
            'counter_id' => $request->counter_id,
            'service_id' => $ticketData['service_id'],
            'q_no' => $ticketData['ticket_no'],
            'q_name' => $request->q_name,
            'noted' => $request->noted,
            'is_called' => 1
        ];

        $procedureParams = [];

        foreach ($TmpParamProc as $key => $value) {
            $procedureParams[] = "'$value'";
        }
        $TmpParamProcedure = "CALL IsCounterCalled(" . implode(', ', $procedureParams) . ")";

        // call store procedure
        $isCalled = DB::select($TmpParamProcedure);
        if (isset($isCalled)) {
            event(new NewMessage($ticketData['ticket_no']));
            return response()->json([
                "success" => true,
                "message" => "Counter called is successfully",
            ], 200);
        } else {
            return response()->json([
                "success" => false,
                "message" => "Counter called is fail!",
            ], 200);
        }
    }
}
