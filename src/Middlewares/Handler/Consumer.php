<?php
namespace Metamorphosis\Middlewares\Handler;

use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;

class Consumer implements MiddlewareInterface
{
    /**
     * @var ConsumerTopicHandler
     */
    protected $consumerTopicHandler;

    public function __construct(ConsumerTopicHandler $consumerTopicHandler)
    {
        $this->consumerTopicHandler = $consumerTopicHandler;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->consumerTopicHandler->handle($record);
    }
}
