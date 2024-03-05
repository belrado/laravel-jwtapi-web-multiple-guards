<?php

namespace App\Facade;

use Illuminate\Support\Facades\Facade;

class CommonFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'common';
    }
}
