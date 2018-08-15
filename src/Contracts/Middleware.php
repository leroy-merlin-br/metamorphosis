<?php
namespace Metamorphosis\Contracts;

use Metamorphosis\Message;

interface Middleware
{
    /**
     * @param Message           $message
     * @param MiddlewareHandler $handler
     */
    public function process(Message $message, MiddlewareHandler $handler): void;
}
