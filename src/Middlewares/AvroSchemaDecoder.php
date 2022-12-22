<?php

namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Record\RecordInterface;

class AvroSchemaDecoder implements MiddlewareInterface
{
    private MessageDecoder $decoder;

    private AbstractConfigManager $configManager;

    public function __construct(AbstractConfigManager $configManager, ClientFactory $factory)
    {
        $this->configManager = $configManager;
        if (!$this->configManager->get('url')) {
            throw new ConfigurationException(
                "Avro schema url not found, it's required to use AvroSchemaDecoder Middleware"
            );
        }

        $this->decoder = new MessageDecoder($factory->make($configManager));
    }

    public function process(RecordInterface $record, Closure $next)
    {
        $record->setPayload(
            $this->decoder->decodeMessage($record->getPayload())
        );

        return $next($record);
    }
}
