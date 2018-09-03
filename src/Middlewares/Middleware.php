<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Record\Record;

interface Middleware
{
    /**
     * @param Record            $record
     * @param MiddlewareHandler $handler
     */
    public function process(Record $record, MiddlewareHandler $handler): void;
}
