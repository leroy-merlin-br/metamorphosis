<?php

namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;

class AvroSchemaDecoder implements MiddlewareInterface
{
    private MessageDecoder $decoder;

    public function __construct(ClientFactory $factory, ConsumerConfigOptions $consumerConfigOptions)
    {
        if (!$consumerConfigOptions->getAvroSchema()->getUrl()) {
            throw new ConfigurationException(
                "Avro schema url not found, it's required to use AvroSchemaDecoder Middleware"
            );
        }

        $this->decoder = new MessageDecoder(
            $factory->make($consumerConfigOptions->getAvroSchema())
        );
    }

    public function process(RecordInterface $record, Closure $next)
    {
        $record->setPayload(
            $this->decoder->decodeMessage($record->getPayload())
        );

        return $next($record);
    }
}
