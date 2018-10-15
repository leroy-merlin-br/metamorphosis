<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

class Dispatcher extends AbstractMiddlewareHandler
{
    public function handle(RecordInterface $record): void
    {
        reset($this->queue);
        $iterator = new Iterator($this->queue);
        $iterator->handle($record);
    }
}
