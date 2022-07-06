<?php

namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\AbstractConfigManager;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Record\RecordInterface;

/**
 * Fetches a schema for a topic by subject and version (currently only 'latest')
 * and then encodes the message by the schema id (not subject and version),
 * that's why it's called "mixed" encoder.
 */
class AvroSchemaMixedEncoder implements MiddlewareInterface
{
    private SchemaId $schemaIdEncoder;

    private CachedSchemaRegistryClient $schemaRegistry;

    private AbstractConfigManager $configManager;

    public function __construct(SchemaId $schemaIdEncoder, ClientFactory $factory, AbstractConfigManager $configManager)
    {
        if (!$configManager->get('url')) {
            throw new ConfigurationException(
                "Avro schema url not found, it's required to use AvroSchemaEncoder Middleware"
            );
        }

        $schemaRegistry = $factory->make($configManager);
        $this->schemaIdEncoder = $schemaIdEncoder;
        $this->schemaRegistry = $schemaRegistry;
        $this->configManager = $configManager;
    }

    public function process(RecordInterface $record, Closure $next)
    {
        $topic = $this->configManager->get('topic_id');
        $schema = $this->schemaRegistry->getBySubjectAndVersion(
            "{$topic}-value",
            'latest'
        );

        $arrayPayload = json_decode($record->getPayload(), true);
        $encodedPayload = $this->schemaIdEncoder->encode(
            $schema,
            $arrayPayload
        );

        $record->setPayload($encodedPayload);

        return $next($record);
    }
}
