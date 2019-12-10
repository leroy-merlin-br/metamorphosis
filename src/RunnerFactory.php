<?php
namespace Metamorphosis;

use Metamorphosis\Connectors\Consumer\ConnectorFactory;

class RunnerFactory
{
    public function make(): AbstractConsumerRunner
    {
        $consumer = ConnectorFactory::make()->getConsumer();
        if (config('kafka.runtime.isAvroSchema')) {
            return app(AvroConsumerRunner::class, compact('consumer'));
        }

        return app(ConsumerRunner::class, compact('consumer'));
    }
}
