<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

abstract class AbstractMiddlewareHandler implements MiddlewareHandler
{
    public function __construct(iterable $queue)
    {
        $this->queue = $queue;
    }

    /**
     * Handles the current entry in the middleware queue and advances.
     *
     * @param Message $message
     */
    abstract public function handle(Message $message): void;
}
