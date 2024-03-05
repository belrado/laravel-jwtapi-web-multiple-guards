<?php

namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class AuthFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'appAuth';
    }
}
