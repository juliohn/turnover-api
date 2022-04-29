<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Balance;
use DateTime;
use JWTAuth;
use App\Http\Requests\ExpenseRequest;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        $data = Balance::where('account_id', $auth->account->id)
                        ->where('type', 'E')
                        ->orderBy('created_at', 'desc')
                        ->get();
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExpenseRequest $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());

        if ($auth->role === 'A') {
            return response()->json(["error" => 1, "message" => "Access denied by this role"], 422);
        }

        $resume = resume_balance($auth);

        if (round($resume->current, 2) < (double) round($request->amount, 2)) {
            return response()->json(["error" => 1, "message" => "Insufficient funds"], 422);
        }

        try {
            $balance = new Balance();
            $balance->account_id = $auth->account->id;
            $balance->amount = $request->amount;
            $balance->description = $request->description;
            $balance->date = $request->date;
            $balance->type = "E";
            $balance->status = "A";
            $balance->image_path = null;
            $balance->created_by = $auth->id;
            $balance->updated_by = $auth->id;
            $balance->created_at = new DateTime();
            $balance->save();

            $result = array(
                'error' => 0,
                'message' => 'Expensive register with success.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(ExpenseRequest $request, $id)
    {
        $auth = JWTAuth::toUser($request->bearerToken());

        $resume = resume_balance($auth);

        if (round($resume->current, 2) < (double) round($request->amount, 2)) {
            return response()->json(["error" => 1, "message" => "Insufficient funds"], 422);
        }

        try {
            $balance = Balance::find($id);
            $balance->amount = $request->amount;
            $balance->description = $request->description;
            $balance->date = $request->date;
            $balance->updated_by = $auth->id;
            $balance->updated_at = new DateTime();
            $balance->update();

            $result = array(
                'error' => 0,
                'message' => 'Expensive updated with success.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        try {
            $expensive = Balance::find($id);
            $expensive->deleted_by = $auth->id;
            $expensive->deleted_at = new DateTime();
            $expensive->update();

            $result = array(
                'error'=> 0,
                'message' => 'Expensive deleted with success.',
            );

            return response()->json($result);
        } catch (\Exception  $e) {
            $result = array(
                'error'=> 1 ,
                'msg'=>$e->getMessage(),
            );
            return response()->json($result);
        }
    }
}
