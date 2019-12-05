<?php
namespace Metamorphosis;

use Metamorphosis\Config\Consumer as ConsumerConfig;
use Metamorphosis\Consumers\ConsumerInterface;

class RunnerFactory
{
    public function make(ConsumerConfig $config, ConsumerInterface $consumer, int $timeout = null): AbstractConsumerRunner
    {
        if (!$timeout) {
            $timeout = 2000000;
        }

        if ($config->isAvroSchema()) {
            return new AvroConsumerRunner($config, $consumer, $timeout);
        }

        return new ConsumerRunner($config, $consumer, $timeout);
    }
}
