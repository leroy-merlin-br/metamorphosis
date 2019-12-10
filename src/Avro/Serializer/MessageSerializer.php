<?php
namespace Metamorphosis\Avro\Serializer;

use AvroIO;
use AvroIOBinaryDecoder;
use AvroIOBinaryEncoder;
use AvroIODatumReader;
use AvroIODatumWriter;
use AvroSchema;
use AvroStringIO;
use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use RuntimeException;

class MessageSerializer
{
    const MAGIC_BYTE_SCHEMAID = 0;

    const MAGIC_BYTE_SUBJECT_VERSION = 1;

    /**
     * @var array
     */
    private $idToDecoderFunc = [];

    /**
     * @var array
     */
    private $subjectVersionToDecoderFunc = [];

    /**
     * @var AvroIODatumWriter[]
     */
    private $idToWriters = [];

    /**
     * @var AvroIODatumWriter[][]
     */
    private $subjectVersionToWriters = [];

    /**
     * @var CachedSchemaRegistryClient
     */
    private $registry;

    /**
     * @var bool
     */
    private $registerMissingSchemas;

    /**
     * @var int
     */
    private $defaultEncodingFormat;

    public function __construct(CachedSchemaRegistryClient $registry, array $options = [])
    {
        $this->registry = $registry;

        $this->registerMissingSchemas = $options['register_missing_schemas'] ?? false;
        $this->defaultEncodingFormat = $options['default_encoding_format'] ?? static::MAGIC_BYTE_SCHEMAID;
    }

    /**
     * Given a parsed Avro schema, encode a record for the given topic.
     * The schema is registered with the subject of 'topic-value'
     *
     * @param string     $topic  Topic name
     * @param AvroSchema $schema Avro Schema
     * @param mixed      $record An record (object, array, string, etc) to serialize
     * @param bool       $isKey  If the record is a key
     * @param int|null   $format Encoding Format
     *
     * @throws \AvroIOException
     * @return string Encoded record with schema ID as bytes
     */
    public function encodeRecordWithSchema(
        string $topic,
        AvroSchema $schema,
        $record,
        bool $isKey = false,
        int $format = null
    ): string {
        $suffix = $isKey ? '-key' : '-value';
        $subject = $topic.$suffix;

        $format = $format ?? $this->defaultEncodingFormat;

        if ($format === static::MAGIC_BYTE_SUBJECT_VERSION) {
            try {
                $version = $this->registry->getSchemaVersion($subject, $schema);
            } catch (RuntimeException $e) {
                if ($this->registerMissingSchemas) {
                    $this->registry->register($subject, $schema);
                    $version = $this->registry->getSchemaVersion($subject, $schema);
                } else {
                    throw $e;
                }
            }

            $this->subjectVersionToWriters[$subject][$version] = new AvroIODatumWriter($schema);

            return $this->encodeRecordWithSubjectAndVersion($subject, $version, $record);
        }

        if ($format === static::MAGIC_BYTE_SCHEMAID) {
            try {
                $id = $this->registry->getSchemaId($subject, $schema);
            } catch (RuntimeException $e) {
                if ($this->registerMissingSchemas) {
                    $this->registry->register($subject, $schema);
                    $id = $this->registry->getSchemaId($subject, $schema);
                } else {
                    throw $e;
                }
            }

            $this->idToWriters[$id] = new AvroIODatumWriter($schema);

            return $this->encodeRecordWithSchemaId($id, $record);
        }

        throw new RuntimeException('Unsuported format: '.$format);
    }

    /**
     * Decode a message from kafka that has been encoded for use with the schema registry.
     *
     * @param string $message
     *
     * @return mixed
     * @throws \AvroIOException
     */
    public function decodeMessage(string $message)
    {
        if (!$message) {
            throw new RuntimeException('Message is too small to decode');
        }

        $io = new AvroStringIO($message);

        $magicByte = unpack('C', $io->read(1));
        $magicByte = $magicByte[1];

        if ($magicByte === static::MAGIC_BYTE_SCHEMAID) {
            $id = unpack('N', $io->read(4));
            $id = $id[1];

            $decoder = $this->getDecoderById($id);

            return $decoder($io);
        }

        if ($magicByte === static::MAGIC_BYTE_SUBJECT_VERSION) {
            $size = $io->read(4);
            $subjectSize = unpack('N', $size);
            $subjectBytes = unpack('C*', $io->read($subjectSize[1]));
            $version = unpack('N', $io->read(4));

            $version = $version[1];

            $subject = '';
            foreach ($subjectBytes as $subjectByte) {
                $subject .= chr($subjectByte);
            }

            $decoder = $this->getDecoderBySubjectAndVersion($subject, $version);

            return $decoder($io);
        }

        return $message;
    }

    /**
     * Encode a record with a given schema id.
     *
     * @param int   $schemaId
     * @param mixed $record A data to serialize
     *
     * @throws \AvroIOException
     */
    private function encodeRecordWithSchemaId(int $schemaId, $record): string
    {
        $writer = $this->idToWriters[$schemaId];

        $io = new AvroStringIO();

        // write the header

        // magic byte
        $io->write(pack('C', static::MAGIC_BYTE_SCHEMAID));

        // write the schema ID in network byte order (big end)
        $io->write(pack('N', $schemaId));

        // write the record to the rest of it
        // Create an encoder that we'll write to
        $encoder = new AvroIOBinaryEncoder($io);

        // write the object in 'obj' as Avro to the fake file...
        $writer->write($record, $encoder);

        return $io->string();
    }

    /**
     * Encode a record with a given schema id.
     *
     * @param string $subject
     * @param int    $version
     * @param mixed  $record A data to serialize
     *
     * @throws \AvroIOException
     */
    private function encodeRecordWithSubjectAndVersion(string $subject, int $version, $record): string
    {
        $writer = $this->subjectVersionToWriters[$subject][$version];

        $io = new AvroStringIO();

        // write the header

        // magic byte
        $io->write(pack('C', static::MAGIC_BYTE_SUBJECT_VERSION));

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
        $writer->write($record, $encoder);

        return $io->string();
    }

    private function getDecoderById(int $schemaId): Closure
    {
        if (!isset($this->idToDecoderFunc[$schemaId])) {
            $schema = $this->registry->getById($schemaId);

            $reader = new AvroIODatumReader($schema);

            $this->idToDecoderFunc[$schemaId] = function (AvroIO $io) use ($reader) {
                return $reader->read(new AvroIOBinaryDecoder($io));
            };
        }

        return $this->idToDecoderFunc[$schemaId];
    }

    private function getDecoderBySubjectAndVersion(string $subject, int $version): Closure
    {
        if (!isset($this->subjectVersionToDecoderFunc[$subject][$version])) {
            $schema = $this->registry->getBySubjectAndVersion($subject, $version);

            $reader = new AvroIODatumReader($schema);

            if (!isset($this->subjectVersionToDecoderFunc[$subject])) {
                $this->subjectVersionToDecoderFunc[$subject] = [];
            }

            $this->subjectVersionToDecoderFunc[$subject][$version] = function (AvroIO $io) use ($reader) {
                return $reader->read(new AvroIOBinaryDecoder($io));
            };
        }

        return $this->subjectVersionToDecoderFunc[$subject][$version];
    }
}
