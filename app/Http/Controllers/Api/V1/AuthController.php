<?php
namespace App\Http\Controllers\Api\V1;

use App\Facade\AuthFacade;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Facade\CommonFacade;

use App\Constants\Response as Constants;

class AuthController extends Controller
{
    public bool $token = true;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'nick_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => '0001', 'message' => $validator->errors()]);
        }

        try {
            $user = new User();
            $user->name = $request->name;
            $user->nick_name = $request->nick_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->auth_token = ''; //CommonFacade::getRandomCode();
            $user->phone = $request->phone ?? '';
            $user->save();
            /*
            회원 가입과 동시에 로그인(토큰 발급) 하는 방식
             if ($this->token) {
                return $this->login($request);
             }
            */
            return response()->json(['resultCode' => Constants::SUCCESS, 'message' => '회원 가입 성공', 'data' => $user], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json(['resultCode' => Constants::FAIL, 'message' => $e]);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);

            if (!$jwt_token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'resultCode' => Constants::FAIL,
                    'message' => '아이디 또는 비밀번호가 일치하지 않습니다.',
                ]);
            }

            if ($user = Auth::user()) {
                // 접근권한 검사
                if ($user->use !== 'y' || $user->type !== 'admin') {
                    return response()->json([
                        'success' => Constants::FAIL,
                        'message' => '정지된 계정 이거나 접근 권한이 없습니다.',
                    ]);
                }

                // auth_token 발급 (jwt refresh token 발급을 위한 회원 고유 키값)
                if ($auth_token = AuthFacade::setUserAuthToken(['email' => $credentials['email']])) {
                    AuthFacade::updateLastLoginTime($user->no);
                    $user->auth_token = $auth_token;

                    return response()->json([
                        'resultCode' => Constants::SUCCESS,
                        'token' => $jwt_token,
                        'user' => $user,
                    ]);
                }
            }

            return response()->json([
                'resultCode' => Constants::FAIL,
                'message' => '회원 정보가 없습니다.',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'resultCode' => Constants::ERROR,
                'message' => '아이디 또는 비밀번호가 일치하지 않습니다.',
            ]);
        }
    }

    public function logout(Request $request)
    {
        try {
            $pkNo = JWTAuth::payload()->get('sub');
            JWTAuth::invalidate(JWTAuth::parseToken());

            return response()->json([
                'result_code' => 0000,
                'message' => 'User logged out successfully'
            ]);

        } catch (JWTException $exception) {
            return response()->json([
                'result_code' => 0001,
                'message' => 'Sorry, the user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getUser(Request $request)
    {
        try{
            $user = JWTAuth::authenticate();
            return response()->json([
                'resultCode' => Constants::SUCCESS,
                'user' => $user
            ]);

        } catch(Exception $e){
            return response()->json([
                'resultCode' => Constants::FAIL,
                'message'=>'something went wrong'
            ]);
        }
    }

    public function refreshToken(Request $request)
    {
        try {
            $user = User::select('auth_token')->where('email', $request->email)->where('no', $request->pkNo)->first();

            if (!empty($user->auth_token) && $user->auth_token === $request->auth_token) {
                if($auth_token = AuthFacade::setUserAuthToken(['no' => $request->pkNo])) {
                    AuthFacade::updateLastLoginTime($request->pkNo);
                    return response()->json([
                        'resultCode' => Constants::SUCCESS,
                        'token' => $request->refreshToken,
                        'authToken' => $auth_token
                    ]);
                }
            }

            return response()->json([
                'resultCode' => Constants::UNAUTHORIZED,
                'message' => 'Invalid token auth_token'
            ], Response::HTTP_UNAUTHORIZED);

        } catch(Exception $e){
            return response()->json([
                'resultCode' => Constants::ERROR,
                'message'=>'something went wrong'
            ]);
        }
    }

    public function updateMyPassword(Request $request)
    {
        $params = $request->input();
        $validator = Validator::make($params, [
            'password' => 'required',
            'new_password' => 'required',
            'c_password' => 'required|same:new_password',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Constants::FAIL, 'message' => $validator->errors()]);
        }

        $credentials['password'] = $params['password'];

        try {
            $pkNo = JWTAuth::payload()->get('sub');
            $credentials['email'] = JWTAuth::authenticate()->email;
            if (!$jwt_token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'resultCode' => Constants::FAIL,
                    'message' => '비밀번호가 일치하지 않습니다.',
                ]);
            }

        } catch (Exception $e) {
            try {
                $pkNo = Auth::user()->no;
                $credentials['email'] = Auth::user()->email;
                if (!Auth::guard('web')->attempt($credentials)) {
                    return response()->json([
                        'resultCode' => Constants::FAIL,
                        'message' => '비밀번호가 일치하지 않습니다.',
                    ]);
                }
            } catch (Exception $e) {
                return response()->json([
                    'resultCode' => Constants::ERROR,
                    'message' => '네트워크 에러 잠시 후 다시 시도해 주세요.',
                ]);
            }
        }

        if ($params['new_password'] === $params['password']) {
            return response()->json([
                'resultCode' => Constants::FAIL,
                'message' => '비밀번호가 동일합니다.',
            ]);
        }

        $password = bcrypt($params['new_password']);

        if (AuthFacade::updateUserPassword($pkNo, $password)) {
            return response()->json([
                'resultCode' => Constants::SUCCESS,
            ]);
        }

        return response()->json([
            'resultCode' => Constants::FAIL,
            'message' => '비밀번호 저장 실패 잠시 후 다시 시도해 주세요.',
        ]);
    }

    public function updateMyNickname(Request $request)
    {
        $params = $request->input();

        $validator = Validator::make($params, [
            'nick_name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCode' => Constants::FAIL, 'message' => $validator->errors()]);
        }

        try {
            $pkNo = JWTAuth::payload()->get('sub');
        } catch (Exception $e) {
            try {
                $pkNo = Auth::user()->no;
            } catch (Exception $e) {
                return response()->json([
                    'resultCode' => Constants::ERROR,
                    'message' => '네트워크 에러 잠시 후 다시 시도해 주세요.',
                ]);
            }
        }

        if (!empty($pkNo)) {
            if (User::where('nick_name', $params['nick_name'])->first()) {
                return response()->json([
                    'resultCode' => Constants::FAIL,
                    'message' => $params['nick_name'].' 현재 사용중인 닉네임 입니다.',
                ]);
            }

            if (AuthFacade::updateUserNickname($pkNo, $params['nick_name'])) {
                return response()->json([
                    'resultCode' => Constants::SUCCESS,
                ]);
            } else {
                return response()->json([
                    'resultCode' => Constants::FAIL,
                    'message' => '닉네임 저장 실패 잠시 후 다시 시도해 주세요.',
                ]);
            }
        }

        return response()->json([
            'resultCode' => Constants::ERROR,
            'message' => '네트워크 에러 잠시 후 다시 시도해 주세요.',
        ]);
    }
}
