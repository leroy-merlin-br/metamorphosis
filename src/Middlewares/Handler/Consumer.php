<?php
namespace Metamorphosis\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;

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

    public function process(RecordInterface $record, Closure $next)
    {
        $this->consumerTopicHandler->handle($record);
    }
}
