<?php
namespace Metamorphosis\Avro\Serializer\Encoders;

use AvroSchema;

interface EncoderInterface
{
    /**
     * Given a parsed Avro schema, encode a record for the given topic.
     * The schema is registered with the subject of 'topic-value'
     *
     * @param string     $subject Subject name
     * @param AvroSchema $schema  Avro Schema
     * @param mixed      $message An record (object, array, string, etc) to serialize
     *
     * @return string Encoded record with schema ID as bytes
     */
    public function encode(string $subject, AvroSchema $schema, $message, bool $registerMissingSchemas): string;
}
