<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record;

interface MiddlewareHandler
{
    /**
     * @param Record $record
     */
    public function handle(Record $record): void;
}
