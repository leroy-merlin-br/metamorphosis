<?php
namespace Tests\Unit\Dummies;

use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class MiddlewareDummy implements MiddlewareInterface
{
    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $handler->handle($record);
    }
}
