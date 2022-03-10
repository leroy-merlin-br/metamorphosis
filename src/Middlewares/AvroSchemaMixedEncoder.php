<?php
namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ConfigOptionsProducer;

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

    /**
     * @var ConfigOptionsProducer
     */
    private $configOptionsProducer;

    public function __construct(SchemaId $schemaIdEncoder, ClientFactory $factory, ConfigOptionsProducer $configOptionsProducer)
    {
        if (!$configOptionsProducer->getAvroSchema()->getUrl()) {
            throw new ConfigurationException("Avro schema url not found, it's required to use AvroSchemaEncoder Middleware");
        }

//        $schemaRegistry = $factory->make($configOptionsProducer->getAvroSchema());
        $this->schemaIdEncoder = $schemaIdEncoder;
        $this->schemaRegistry = $schemaIdEncoder->getRegistry();
        $this->configOptionsProducer = $configOptionsProducer;
    }

    public function process(RecordInterface $record, Closure $next)
    {
        $topic = $this->configOptionsProducer->getTopicId();
        $schema = $this->schemaRegistry->getBySubjectAndVersion("{$topic}-value", 'latest');
        $arrayPayload = json_decode($record->getPayload(), true);
        $encodedPayload = $this->schemaIdEncoder->encode($schema, $arrayPayload);

        $record->setPayload($encodedPayload);

        return $next($record);
    }
}
