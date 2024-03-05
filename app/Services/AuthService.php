<?php

namespace App\Services;

use App\Facade\CommonFacade;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthService
{
    protected Request $request;

    public function __construct()
    {
    }

    public function setAuthApiWebConv()
    {

    }

    /**
     * @param array $params : [email | no => value]
     * @return false | string
     *
     * email 또는 pk 로 auth_token db users 에 update 및 auth_token 값 리턴
     */
    public function setUserAuthToken(array $params = ['email' => ''], $empty = false): false | string
    {
        $validate = ['no', 'email'];
        $key = array_keys($params)[0];
        $value = $params[$key] ?? null;
        if (! in_array($key, $validate)) return false;
        if (empty($value)) return false;

        $auth_token = $empty ? '' : CommonFacade::getRandomCode();
        try {
            User::where($key, $value)->update(['auth_token' => $auth_token]);
            return $auth_token;
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'setUserAuthToken', 'message' => $e]));
            return false;
        }
    }

    public function updateLastLoginTime($pkNo): void
    {
        try {
            User::where('no', $pkNo)->update(['last_login_at' => date('Y-m-d H:i:s', time())]);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateLastLoginTime', 'message' => $e]));
        }
    }

    public function updateUserPassword($pkNo, $password)
    {
        try {
            return User::where('no', $pkNo)->update(['password' => $password]);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateUserPassword', 'message' => $e]));
            return false;
        }
    }

    public function updateUserNickname($pkNo, $nickname)
    {
        try {
            return User::where('no', $pkNo)->update(['nick_name' => $nickname]);
        } catch (QueryException $e) {
            Log::error(json_encode(['type' => 'DB Query', 'error' => 'updateUserNickname', 'message' => $e]));
            return false;
        }
    }
}
