<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Record;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

interface Middleware
{
    /**
     * @param Record            $record
     * @param MiddlewareHandler $handler
     */
    public function process(Record $record, MiddlewareHandler $handler): void;
}
