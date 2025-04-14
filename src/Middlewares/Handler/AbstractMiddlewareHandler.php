<?php

namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;
use Override;

abstract class AbstractMiddlewareHandler implements MiddlewareHandlerInterface
{
    /**
     * @var iterable<mixed>
     */
    protected iterable $queue;

    /**
     * Handles the current entry in the middleware queue and advances.
     */
    #[Override]
    abstract public function handle(RecordInterface $record);

    public function __construct(iterable $queue)
    {
        $this->queue = $queue;
    }
}
