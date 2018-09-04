<?php
namespace Tests\Dummies;

use Metamorphosis\Record\Record;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Middlewares\Middleware;

class MiddlewareDummy implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $handler->handle($record);
    }
}
