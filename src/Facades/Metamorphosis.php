<?php
namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void produce(string|array $data, string $topic, int $partition = null, int $key = null)
 */
class Metamorphosis extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'metamorphosis';
    }
}
