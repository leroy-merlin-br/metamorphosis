<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\RecordInterface;

abstract class AbstractMiddlewareHandler implements MiddlewareHandlerInterface
{
    /**
     * @var iterable
     */
    protected $queue;

    public function __construct(iterable $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Handles the current entry in the middleware queue and advances.
     */
    abstract public function handle(RecordInterface $record);
}
