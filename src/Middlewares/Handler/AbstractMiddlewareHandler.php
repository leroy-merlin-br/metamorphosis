<?php

namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

abstract class AbstractMiddlewareHandler implements MiddlewareHandlerInterface
{
    /**
     * @var array<mixed>
     */
    protected array $queue;

    /**
     * Handles the current entry in the middleware queue and advances.
     */
    abstract public function handle(RecordInterface $record);

    public function __construct(array $queue)
    {
        $this->queue = $queue;
    }
}
