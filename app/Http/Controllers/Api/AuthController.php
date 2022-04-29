<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Mail\SendEmail;
use stdClass;
use JWTAuth;
use DateTime;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only(['email', 'password']);


        if (!$token = auth('api')->setTTL(60*24*30*12*10)->attempt($credentials)) {
            $result = array(
                'error' => 1,
                'message' => 'Dados de acesso inválidos',
            );
            return response()->json($result, 409);
        }

        $user = new stdClass();
        $user->id = auth('api')->user()->id;
        $user->name = auth('api')->user()->name;
        $user->email = auth('api')->user()->email;
        $user->role = auth('api')->user()->role;
        return $this->respondWithToken($token, $user);
    }




    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $user)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' =>$user
        ]);
    }
}
