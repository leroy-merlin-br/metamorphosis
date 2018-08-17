<?php
namespace Tests\Dummies;

use Metamorphosis\Message;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;
use Metamorphosis\Middlewares\Middleware;

class MiddlewareDummy implements Middleware
{
    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $handler->handle($message);
    }
}
