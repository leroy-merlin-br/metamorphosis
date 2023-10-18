<?php

namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;

class Metamorphosis extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'metamorphosis';
    }
}
