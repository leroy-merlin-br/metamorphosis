<?php
namespace Tests\Unit\Dummies;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class MiddlewareDummy implements MiddlewareInterface
{
    public function process(RecordInterface $record, Closure $next)
    {
        return $next($record);
    }
}
