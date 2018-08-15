<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\Contracts\ConsumerTopicHandler;
use Metamorphosis\Contracts\Middleware;
use Metamorphosis\Message;

class Consumer implements Middleware
{
    public function __construct(ConsumerTopicHandler $consumerTopicHandler)
    {
        $this->consumerTopicHandler = $consumerTopicHandler;
    }

    public function process(Message $message, MiddlewareHandler $handler): void
    {
        $this->consumerTopicHandler->handle($message);
    }
}
