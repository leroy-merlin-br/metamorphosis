<?php
namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed get(string $key = null)
 * @method static void set(array $config)
 * @method static bool has(string $key)
 * @method static array middlewares()
 */
class ConfigManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'managerConfig';
    }
}
