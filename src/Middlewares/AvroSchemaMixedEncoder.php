<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Facades\ConfigManager;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\RecordInterface;

/**
 * Fetches a schema for a topic by subject and version (currently only 'latest')
 * and then encodes the message by the schema id (not subject and version),
 * that's why it's called "mixed" encoder.
 */
class AvroSchemaMixedEncoder implements MiddlewareInterface
{
    /**
     * @var SchemaId
     */
    private $schemaIdEncoder;

    /**
     * @var CachedSchemaRegistryClient
     */
    private $schemaRegistry;

    public function __construct(SchemaId $schemaIdEncoder, CachedSchemaRegistryClient $schemaRegistry)
    {
        if (!ConfigManager::get('url')) {
            throw new ConfigurationException("Avro schema url not found, it's required to use AvroSchemaEncoder Middleware");
        }

        $this->schemaIdEncoder = $schemaIdEncoder;
        $this->schemaRegistry = $schemaRegistry;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $topic = ConfigManager::get('topic_id');
        $schema = $this->schemaRegistry->getBySubjectAndVersion("{$topic}-value", 'latest');

        $arrayPayload = json_decode($record->getPayload(), true);
        $encodedPayload = $this->schemaIdEncoder->encode($schema, $arrayPayload);

        $record->setPayload($encodedPayload);
        $handler->handle($record);
    }
}
