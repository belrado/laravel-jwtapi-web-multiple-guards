<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JwtRefresh
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse
     *
     * JWT_REFRESH_TTL 만료전 jwt 토큰 JWT_TTL refresh
     * JWT_REFRESH_TTL 시간이 지나면 토큰 사용 못함 새로 로그인 해야함
     *
     * app 에서 jwt 토큰 만료 기간 없이 사용할 경우를 대비 및 보안을 위해 --
     * refresh 토큰 전송은 로그인 시 발행한 auth_token 을 비교 한뒤 전송
     * 토큰 재발행 시 auth_token 갱신
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        if (!empty(request()->auth_token) && !empty($request->email)) {
            try {
                JWTAuth::parseToken()->authenticate();
                $request->pkNo = JWTAuth::payload()->get('sub');
                $request->refreshToken = JWTAuth::refresh();
                return $next($request);

            } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
                try {
                    $payload = JWTAuth::manager()->getJWTProvider()->decode($request->bearerToken());
                    $request->refreshToken = JWTAuth::refresh();
                    $request->pkNo = $payload['sub'];
                    return $next($request);

                } catch (\Exception $e) {
                    $message = 'Token expired';
                }

            } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
                $message = 'Invalid token';
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                $message = 'Provide token';
            }
        } else {
            $message = 'Invalid token';
        }

        return response()->json(['resultCode' => '0002', 'message' => $message], 401);
    }
}
