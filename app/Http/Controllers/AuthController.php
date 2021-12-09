<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\HttpStatusCodes;
use \Firebase\JWT\JWT;
use Validator;
use Carbon\Carbon;
// MODEL
use App\User as ModelUser;

class AuthController extends Controller
{

    public function __construct()
    {
        Carbon::setLocale('id');
    }

    public function login(Request $term) {
        $validator = Validator::make($term->all(), 
        [
            'user_name'     => 'required|string',
            'password'      => 'required|string',
        ]);
        if ($validator->fails()) {
           return response()->json([
                'status'    => HttpStatusCodes::HTTP_BAD_REQUEST,
                'error'     => true,
                'message'   => $validator->errors()->all()[0]
           ],HttpStatusCodes::HTTP_BAD_REQUEST);
        }
        $user = ModelUser::where('user_name','=',$term->user_name)->first();
        if(!$user) {
            return response()->json([
                'status'    => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error'     => true,
                'message'   => 'username not found'
            ],HttpStatusCodes::HTTP_UNAUTHORIZED);
        }
        if(md5($term->password) != $user->password) {
            return response()->json([
                'status'    => HttpStatusCodes::HTTP_UNAUTHORIZED,
                'error'     => true,
                'message'   => 'Password invalid'
           ],HttpStatusCodes::HTTP_UNAUTHORIZED);
        }
        $payload = array(
            "iss"   => env('JWT_ISS_NAME'),
            "iat"   => time(),
            "exp"   => Carbon::now()->addDays(env('JWT_EXPIRED_DAYS'))->timestamp,
            "nbf"   => time(),
            "user"  => array(
                "id"        => $user->id,
                "username"  => $user->user_name                   
            )
        );
        return response()->json([
            'status'    => HttpStatusCodes::HTTP_OK,
            'error'     => false,
            'message'   => 'Successfully login',
            'data'      => array(
                'token'                     => JWT::encode($payload, env('JWT_SECRET_KEY')),
                'user'                      => $user
            ),
        ],HttpStatusCodes::HTTP_OK);

    } 
    
}
