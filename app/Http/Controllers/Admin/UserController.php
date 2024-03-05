<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function userList()
    {
        $pageTitle = '회원 목록';
        return view('pages.admin.user.list', compact('pageTitle'));
    }

    public function userDetail($no)
    {
        return view('pages.admin.user.detail');
    }

    public function userRegister()
    {

    }

    public function userUpdate()
    {

    }

    public function userDelete()
    {

    }
}
