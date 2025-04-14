<?php

namespace Metamorphosis\Middlewares\Handler;

use Closure;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\Handler as ConsumerTopicHandler;
use Override;

class Consumer implements MiddlewareInterface
{
    protected ConsumerTopicHandler $consumerTopicHandler;

    public function __construct(ConsumerTopicHandler $consumerTopicHandler)
    {
        $this->consumerTopicHandler = $consumerTopicHandler;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @return mixed
     */
    #[Override]
    public function process(RecordInterface $record, Closure $next)
    {
        $this->consumerTopicHandler->handle($record);
    }
}
