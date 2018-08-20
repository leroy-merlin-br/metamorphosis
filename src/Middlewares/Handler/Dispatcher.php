<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record;

class Dispatcher extends AbstractMiddlewareHandler
{
    public function handle(Record $record): void
    {
        reset($this->queue);
        $iterator = new Iterator($this->queue);
        $iterator->handle($record);
    }
}
