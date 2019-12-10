<?php
namespace Metamorphosis\Avro\Serializer\Encoders;

use AvroIOBinaryEncoder;
use AvroIODatumWriter;
use AvroSchema;
use AvroStringIO;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\SchemaFormats;
use RuntimeException;

class SchemaSubjectAndVersion implements EncoderInterface
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
            $version = $this->registry->getSchemaVersion($subject, $schema);
        } catch (RuntimeException $e) {
            if ($registerMissingSchemas) {
                $this->registry->register($subject, $schema);
                $version = $this->registry->getSchemaVersion($subject, $schema);
            } else {
                throw $e;
            }
        }

        $writer = new AvroIODatumWriter($schema);
        $io = new AvroStringIO();

        // write the header

        // magic byte
        $io->write(pack('C', SchemaFormats::MAGIC_BYTE_SUBJECT_VERSION));

        // write the subject length in network byte order (big end)
        $io->write(pack('N', strlen($subject)));

        // then the subject
        foreach (str_split($subject) as $letter) {
            $io->write(pack('C', ord($letter)));
        }

        // and finally the version
        $io->write(pack('N', $version));

        // write the record to the rest of it
        // Create an encoder that we'll write to
        $encoder = new AvroIOBinaryEncoder($io);

        // write the object in 'obj' as Avro to the fake file...
        $writer->write($message, $encoder);

        return $io->string();
    }
}
