<?php
namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key):
 * @method static void set(array $config)
 * @method static array middlewares(string $key)
 */
class Manager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'manager';
    }
}
