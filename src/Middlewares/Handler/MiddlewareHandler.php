<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\Record;

interface MiddlewareHandler
{
    /**
     * @param Record $record
     */
    public function handle(Record $record): void;
}
