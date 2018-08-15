<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Contracts\MiddlewareHandler;
use Metamorphosis\Message;

class Consumer implements Middleware
{
    public function __construct(ConsumerTopicHandler $consumerTopicHandler)
    {
        dump(__METHOD__);
        $this->consumerTopicHandler = $consumerTopicHandler;
    }

    public function process(Message $message, MiddlewareHandler $handler): void
    {
        dump(__METHOD__);
        $this->consumerTopicHandler->handle($message);
    }
}
