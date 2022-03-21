<?php
namespace Metamorphosis\Facades;

use Illuminate\Support\Facades\Facade;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

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
