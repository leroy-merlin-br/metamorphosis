<?php
namespace Metamorphosis;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Connectors\Consumer\ConnectorFactory;

class RunnerFactory
{
    public function make(): AbstractConsumerRunner
    {
        $consumer = ConnectorFactory::make()->getConsumer();
        if (config('kafka.runtime.use_avro_schema')) {
            $cachedSchema = new CachedSchemaRegistryClient(
                config('kafka.runtime.schema_uri')
            );

            return app(AvroConsumerRunner::class, compact('consumer', 'cachedSchema'));
        }

        return app(ConsumerRunner::class, compact('consumer'));
    }
}
