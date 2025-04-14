<?php

namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;
use Override;

class Dispatcher extends AbstractMiddlewareHandler
{
    #[Override]
    public function handle(RecordInterface $record)
    {
        reset($this->queue);
        $iterator = new Iterator($this->queue);

        return $iterator->handle($record);
    }
}
