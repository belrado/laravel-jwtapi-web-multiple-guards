<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function dashboard()
    {
        $pageTitle = '설정';
        return view('pages.admin.setting.dashboard', compact('pageTitle'));
    }
}
