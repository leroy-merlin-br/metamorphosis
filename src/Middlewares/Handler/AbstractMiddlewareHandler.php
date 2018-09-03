<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Record\Record;

abstract class AbstractMiddlewareHandler implements MiddlewareHandler
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
     *
     * @param Record $record
     */
    abstract public function handle(Record $record): void;
}
