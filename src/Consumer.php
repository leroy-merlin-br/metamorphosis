<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;

class Consumer
{
    private ConsumerInterface $consumer;

    private Dispatcher $dispatcher;

    public function __construct(ConsumerConfigManager $configManager, ConsumerConfigOptions $configOptions)
    {
        $configManager->set($configOptions->toArray());

        $this->consumer = Factory::getConsumer(true, $configManager);
        $this->dispatcher = new Dispatcher($configManager->middlewares());
    }

    public function consume(): ?RecordInterface
    {
        if ($response = $this->consumer->consume()) {
            $record = app(ConsumerRecord::class, compact('response'));

            return $this->dispatcher->handle($record);
        }

        return null;
    }
}
