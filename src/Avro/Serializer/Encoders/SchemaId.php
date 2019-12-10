<?php
namespace Metamorphosis\Avro\Serializer\Encoders;

use AvroIOBinaryEncoder;
use AvroIODatumWriter;
use AvroSchema;
use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\SchemaFormats;
use RuntimeException;

class SchemaId implements EncoderInterface
{
    /**
     * @var CachedSchemaRegistryClient
     */
    private $registry;

    public function __construct(CachedSchemaRegistryClient $registry)
    {
        $this->registry = $registry;
    }

    public function encode(string $subject, AvroSchema $schema, $message, bool $registerMissingSchemas): string
    {
        try {
            $schemaId = $this->registry->getSchemaId($subject, $schema);
        } catch (RuntimeException $e) {
            if ($registerMissingSchemas) {
                $schemaId = $this->registry->register($subject, $schema);
            } else {
                throw $e;
            }
        }

        $writer = new AvroIODatumWriter($schema);
        $io = new AvroStringIO();

        // write the header

        // magic byte
        $io->write(pack('C', SchemaFormats::MAGIC_BYTE_SCHEMAID));

        // write the schema ID in network byte order (big end)
        $io->write(pack('N', $schemaId));

        // write the record to the rest of it
        // Create an encoder that we'll write to
        $encoder = new AvroIOBinaryEncoder($io);

        // write the object in 'obj' as Avro to the fake file...
        $writer->write($message, $encoder);

        return $io->string();
    }
}
