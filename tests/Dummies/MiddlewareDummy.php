<?php
namespace Tests\Dummies;

use Metamorphosis\Record\RecordInterface;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\MiddlewareInterface;

class MiddlewareDummy implements MiddlewareInterface
{
    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $handler->handle($record);
    }
}
