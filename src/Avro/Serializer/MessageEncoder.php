<?php

namespace Metamorphosis\Avro\Serializer;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Schema;
use Metamorphosis\Avro\Serializer\Encoders\EncoderInterface;
use Metamorphosis\Avro\Serializer\Encoders\SchemaId;
use Metamorphosis\Avro\Serializer\Encoders\SchemaSubjectAndVersion;
use RuntimeException;

class MessageEncoder
{
    /**
     * @var array<int, string> [int Magic Byte => string Schema Decoder Class]
     */
    private array $encoders = [
        SchemaFormats::MAGIC_BYTE_SCHEMAID => SchemaId::class,
        SchemaFormats::MAGIC_BYTE_SUBJECT_VERSION => SchemaSubjectAndVersion::class,
    ];

    private CachedSchemaRegistryClient $registry;

    private bool $registerMissingSchemas;

    private int $defaultEncodingFormat;

    public function __construct(CachedSchemaRegistryClient $registry, array $options = [])
    {
        $this->registry = $registry;

        $this->registerMissingSchemas = $options['register_missing_schemas'] ?? false;
        $this->defaultEncodingFormat = $options['default_encoding_format'] ?? SchemaFormats::MAGIC_BYTE_SCHEMAID;
    }

    /**
     * Given a parsed Avro schema, encode a record for the given topic.
     * The schema is registered with the subject of 'topic-value'
     *
     * @param string   $topic   Topic name
     * @param Schema   $schema  Avro Schema
     * @param mixed    $message An message/record (object, array, string, etc) to serialize
     * @param bool     $isKey   If the record is a key
     * @param int|null $format  Encoding Format
     *
     * @throws \AvroIOException
     *
     * @return string Encoded record with schema ID as bytes
     */
    public function encodeMessage(
        string $topic,
        Schema $schema,
        $message,
        bool $isKey = false,
        ?int $format = null
    ): string {
        $suffix = $isKey ? '-key' : '-value';
        $subject = $topic . $suffix;
        $format = $format ?? $this->defaultEncodingFormat;

        $encoder = $this->getEncoder($format);

        return $encoder->encode($schema, $message);
    }

    private function getEncoder(int $format): EncoderInterface
    {
        if (!$class = $this->encoders[$format] ?? null) {
            throw new RuntimeException('Unsuported format: ' . $format);
        }

        return app($class, ['registry' => $this->registry]);
    }
}
