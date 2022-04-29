<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Balance;
use App\Http\Requests\CheckRequest;
use DateTime;
use JWTAuth;
use Illuminate\Support\Str;

class CheckController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        $data = Balance::orderBy('created_at', 'desc');
        if ($auth->role === 'C') {
            $data->where('account_id', $auth->account->id);
        }

        $data = $data->where('type', 'C')->get();
        return response()->json($data);
    }


    public function incomes(Request $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        $data = Balance::where('account_id', $auth->account->id)
                        ->where('type', 'C')
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

    public function store(CheckRequest $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());

        if ($auth->role === 'A') {
            return response()->json(["error" => 1, "message" => "Access denied by this role"], 422);
        }

        $image_path = Str::random(10).date('mdYHis') . uniqid().".jpg";
        \File::put(
            storage_path('app/public'). '/' . $image_path,
            base64_decode(str_replace("data:image/jpeg;base64,", "", $request->image_path))
        );

        try {
            $balance = new Balance();
            $balance->account_id = $auth->account->id;
            $balance->amount = $request->amount;
            $balance->description = $request->description;
            $balance->date = new DateTime();
            $balance->type = "C";
            $balance->status = "P";
            $balance->image_path = "public/".$image_path;
            $balance->created_by = $auth->id;
            $balance->updated_by = $auth->id;
            $balance->created_at = new DateTime();
            $balance->save();

            $result = array(
                'error' => 0,
                'message' => 'Check register with success.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        if ($auth->role === 'C') {
            return response()->json(["error" => 1, "message" => "Access denied by this role"], 422);
        }

        try {
            $balance = Balance::find($id);
            return response()->json($balance);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $auth = JWTAuth::toUser($request->bearerToken());

        if ($auth->role === 'C') {
            return response()->json(["error" => 1, "message" => "Access denied by this role"], 422);
        }

        try {
            $balance = Balance::find($id);
            $balance->status = $request->status;
            $balance->updated_by = $auth->id;
            $balance->updated_at = new DateTime();
            $balance->update();

            $result = array(
                'error' => 0,
                'message' => 'check updated with success.',
            );

            return response()->json($result);
        } catch (\Exception  $exception) {
            return response()->json(["error" => 1, "message" => $exception->getMessage()]);
        }
    }
}
