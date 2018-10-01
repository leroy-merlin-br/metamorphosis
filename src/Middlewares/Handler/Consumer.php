<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Metamorphosis\Middlewares\Middleware;
use Metamorphosis\Record\Record;

class Consumer implements Middleware
{
    /**
     * @var ConsumerTopicHandler
     */
    protected $consumerTopicHandler;

    public function __construct(ConsumerTopicHandler $consumerTopicHandler)
    {
        $this->consumerTopicHandler = $consumerTopicHandler;
    }

    public function process(Record $record, MiddlewareHandler $handler): void
    {
        $this->consumerTopicHandler->handle($record);
    }
}
