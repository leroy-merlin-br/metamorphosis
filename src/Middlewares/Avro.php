<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

class Avro implements Middleware
{
    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $message->payload = 'decoded payload';

        $handler->handle($message);
    }
}
