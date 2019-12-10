<?php
namespace Metamorphosis\Avro\Serializer;

use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\Decoders\DecoderInterface;
use Metamorphosis\Avro\Serializer\Decoders\SchemaId;
use Metamorphosis\Avro\Serializer\Decoders\SchemaSubjectAndVersion;
use RuntimeException;

class MessageDecoder
{
    /**
     * @var array [int Magic Byte => string Schema Decoder Class]
     */
    private $decoders = [
        SchemaFormats::MAGIC_BYTE_SCHEMAID => SchemaId::class,
        SchemaFormats::MAGIC_BYTE_SUBJECT_VERSION => SchemaSubjectAndVersion::class,
    ];

    /**
     * @var CachedSchemaRegistryClient
     */
    private $registry;

    public function __construct(CachedSchemaRegistryClient $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Decode a message from kafka that has been encoded for use with the schema registry.
     *
     * @param string $message
     *
     * @throws \AvroIOException
     * @return mixed
     */
    public function decodeMessage(string $message)
    {
        if (!$message) {
            throw new RuntimeException('Message is too small to decode');
        }

        $io = new AvroStringIO($message);

        if (!$decoder = $this->getDecoder($io)) {
            return $message;
        }

        return $decoder->decode($io);
    }

    private function getDecoder(AvroStringIO $io): ?DecoderInterface
    {
        $magicByte = unpack('C', $io->read(1));
        $magicByte = $magicByte[1];

        if (!$class = $this->decoders[$magicByte] ?? null) {
            return null;
        }

        return app($class, ['registry' => $this->registry]);
    }
}
