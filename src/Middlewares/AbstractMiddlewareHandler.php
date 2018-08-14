<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

/**
 * An abstract PSR-15 request handler.
 */
abstract class AbstractMiddlewareHandler implements MiddlewareHandler
{
    /**
     * Constructor.
     *
     * @param array|Traversable $queue    a queue of middleware entries
     * @param callable          $resolver converts queue entries to middleware
     *                                    instances
     */
    public function __construct(iterable $queue)
    {
        // if (!is_iterable($queue)) {
        //     throw new TypeError('\$queue must be array or Traversable.');
        // }

        $this->queue = $queue;
    }

    /**
     * Handles the current entry in the middleware queue and advances.
     *
     * @param Message $message
     */
    abstract public function handle(Message $message): void;
}
