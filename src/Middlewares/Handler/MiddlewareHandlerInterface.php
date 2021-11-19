<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

interface MiddlewareHandlerInterface
{
    public function handle(RecordInterface $record);
}
