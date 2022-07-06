<?php

namespace Metamorphosis\Avro\Serializer\Decoders;

use AvroIOBinaryDecoder;
use AvroIODatumReader;
use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;

class SchemaSubjectAndVersion implements DecoderInterface
{
    private CachedSchemaRegistryClient $registry;

    public function __construct(CachedSchemaRegistryClient $registry)
    {
        $this->registry = $registry;
    }

    public function decode(AvroStringIO $io)
    {
        $size = $io->read(4);
        $subjectSize = unpack('N', $size);
        $subjectBytes = unpack('C*', $io->read($subjectSize[1]));
        $subject = implode(array_map('chr', $subjectBytes));

        $version = unpack('N', $io->read(4));
        $version = $version[1];

        $schema = $this->registry->getBySubjectAndVersion($subject, $version);

        $reader = new AvroIODatumReader($schema->getAvroSchema());

        return $reader->read(new AvroIOBinaryDecoder($io));
    }
}
