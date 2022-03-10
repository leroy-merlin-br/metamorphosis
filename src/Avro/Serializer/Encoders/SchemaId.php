<?php
namespace Metamorphosis\Avro\Serializer\Encoders;

use AvroIOBinaryEncoder;
use AvroIODatumWriter;
use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\CachedSchemaRegistryClientInterface;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\SchemaFormats;

class SchemaId implements EncoderInterface
{
    /**
     * @var CachedSchemaRegistryClient
     */
    private $registry;

    public function __construct(CachedSchemaRegistryClientInterface $registry)
    {
        $this->registry = $registry;
    }

    public function encode(Schema $schema, $message): string
    {
        $schemaId = $schema->getSchemaId();
        $writer = new AvroIODatumWriter($schema->getAvroSchema());
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

    public function getRegistry()
    {
        return $this->registry;
    }
}
