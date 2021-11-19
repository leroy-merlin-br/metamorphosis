<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

class Dispatcher extends AbstractMiddlewareHandler
{
    public function handle(RecordInterface $record)
    {
        reset($this->queue);
        $iterator = new Iterator($this->queue);

        return $iterator->handle($record);
    }
}
