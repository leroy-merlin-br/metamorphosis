<?php

namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

class Metamorphosis extends Facade
{
    #[Override]
    protected static function getFacadeAccessor()
    {
        return 'metamorphosis';
    }
}
