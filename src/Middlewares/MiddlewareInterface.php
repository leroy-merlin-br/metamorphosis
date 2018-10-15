<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\RecordInterface;

interface MiddlewareInterface
{
    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void;
}
