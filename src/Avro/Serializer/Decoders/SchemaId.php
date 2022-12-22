<?php

namespace Metamorphosis\Avro\Serializer\Decoders;

use AvroIOBinaryDecoder;
use AvroIODatumReader;
use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;

class SchemaId implements DecoderInterface
{
    private CachedSchemaRegistryClient $registry;

    public function __construct(CachedSchemaRegistryClient $registry)
    {
        $this->registry = $registry;
    }

    public function decode(AvroStringIO $io)
    {
        $id = unpack('N', $io->read(4));
        $id = $id[1];

        $schema = $this->registry->getById($id);
        $reader = new AvroIODatumReader($schema->getAvroSchema());

        return $reader->read(new AvroIOBinaryDecoder($io));
    }
}
