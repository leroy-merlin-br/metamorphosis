<?php

namespace Metamorphosis\Avro\Serializer\Encoders;

use Metamorphosis\Avro\Schema;

interface EncoderInterface
{
    /**
     * Given a parsed Avro schema, encode a record for the given topic.
     * The schema is registered with the subject of 'topic-value'
     *
     * @param Schema $schema  Avro Schema
     * @param mixed  $message An record (object, array, string, etc) to serialize
     *
     * @return string Encoded record with schema ID as bytes
     */
    public function encode(Schema $schema, $message): string;
}
