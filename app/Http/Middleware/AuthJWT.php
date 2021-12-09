<?php

namespace App\Http\Middleware;

use Closure;
use App\Library\HttpStatusCodes;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use Carbon\Carbon;
use Exception;
// MODEL
use App\User as ModelUser;

class AuthJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');
        if($request->header('Authorization')) {
            try {
                $token      = trim(str_replace('Bearer', '', $request->header('Authorization')));
                $decodeJWT  = JWT::decode($token, env('JWT_SECRET_KEY'), array(env('JWT_ALGORITMA')));
            } catch (Exception $e) {
                return response()->json([
                    'status'        => HttpStatusCodes::HTTP_UNAUTHORIZED,
                    'error'         => true,
                    'message'       => $e->getMessage()
                ], HttpStatusCodes::HTTP_UNAUTHORIZED);
            }
            $user = ModelUser::where('id','=',$decodeJWT->user->id)->first();
            if(!$user) {
                return response()->json([
                    'status'    => HttpStatusCodes::HTTP_UNAUTHORIZED,
                    'error'     => true,
                    'message'   => 'User not found.'
               ],HttpStatusCodes::HTTP_UNAUTHORIZED);
            }
            $request->auth_user     = $user;
            return $next($request);
        }
        return response()->json([
            'status'    => HttpStatusCodes::HTTP_UNAUTHORIZED,
            'error'     => true,
            'message'   => HttpStatusCodes::getMessageForCode(HttpStatusCodes::HTTP_UNAUTHORIZED)
       ], HttpStatusCodes::HTTP_UNAUTHORIZED);
    }
}
