<?php

namespace Metamorphosis;

use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\TopicHandler\ConfigOptions;
use RdKafka\Message;

class Consumer
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    public function __construct(ConsumerConfigManager $configManager, ConfigOptions $configOptions)
    {
        $configManager->set($configOptions->toArray());

        $this->consumer = Factory::getConsumer(true, $configManager);
    }

    public function consume(): ?Message
    {
        return $this->consumer->consume();
    }
}
