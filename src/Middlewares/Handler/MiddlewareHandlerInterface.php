<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

interface MiddlewareHandlerInterface
{
    /**
     * @param RecordInterface $record
     */
    public function handle(RecordInterface $record): void;
}
