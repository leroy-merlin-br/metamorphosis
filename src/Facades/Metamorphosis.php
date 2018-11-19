<?php
namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void produce(HandlerInterface $producerHandler)
 */
class Metamorphosis extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'metamorphosis';
    }
}
