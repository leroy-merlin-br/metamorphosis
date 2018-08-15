<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

interface Middleware
{
    /**
     * @param Message           $message
     * @param MiddlewareHandler $handler
     */
    public function process(Message $message, MiddlewareHandler $handler): void;
}
