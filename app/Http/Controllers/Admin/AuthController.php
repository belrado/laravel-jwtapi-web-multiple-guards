<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Response;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth:web')->except('logout');
    }
    /**
     * Handle an authentication attempt.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        $credentials['use'] = 'y';
        $credentials['type'] = 'admin';

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/admin');
            //return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function onceAuthorize(Request $request)
    {
        $params = $request->input();
        $validator = Validator::make($params, [
            'modifyType' => 'required|in:password,info',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['resultCoded' => Response::FAIL, 'message' => '비밀번호를 정확히 입력해주세요.']);
        }
        $credentials['password'] = $params['password'];
        $credentials['email'] = Auth::user()->email;
        $credentials['use'] = 'y';
        $credentials['type'] = 'admin';

        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->flash('onceAuth', $params['modifyType']);
            return response()->json(['resultCode' => Response::SUCCESS, ''=>$params['modifyType']]);
        }
        return response()->json(['resultCoded' => Response::FAIL, 'message' => '비밀번호를 정확히 입력해주세요.']);
    }

    public function login(Request $request)
    {
        if (Auth::guard('web')->check()) {
            return redirect('/admin');
        } else {
            return view('pages.admin.auth.login');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/admin');
    }

    public function getMyInfo(Request $request)
    {
        $pageTitle = '내 정보';
        $myInfo = Auth::user()->toArray();
        return view('pages.admin.auth.profile', compact('pageTitle', 'myInfo'));
    }

    public function updateMyInfo(Request $request)
    {
        $pageTitle = '내 정보 수정';
        $myInfo = Auth::user()->toArray();
        $onceAuth = $request->session()->get('onceAuth');
        $request->session()->forget('onceAuth');
        return view('pages.admin.auth.profile-edit', compact('pageTitle', 'myInfo', 'onceAuth'));
    }
}
