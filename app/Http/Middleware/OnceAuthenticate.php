<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OnceAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->session()->get('onceAuth')) {
            return $next($request);
        } else {
            return redirect('/admin');
        }
    }
}
