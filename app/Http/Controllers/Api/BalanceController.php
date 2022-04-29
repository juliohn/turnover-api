<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Balance;
use JWTAuth;

class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $auth = JWTAuth::toUser($request->bearerToken());
        $balances = Balance::where('account_id', $auth->account->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        $data["transactions"] = $balances;
        $data["resume"] = resume_balance($auth, true);

        return response()->json($data);
    }
}
