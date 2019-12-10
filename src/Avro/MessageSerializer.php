<?php
namespace Metamorphosis\Avro;

use AvroIO;
use AvroIOBinaryDecoder;
use AvroIOBinaryEncoder;
use AvroIODatumReader;
use AvroIODatumWriter;
use AvroSchema;
use AvroStringIO;
use RuntimeException;

class MessageSerializer
{
    const MAGIC_BYTE_SCHEMAID = 0;
    const MAGIC_BYTE_SUBJECT_VERSION = 1;

    private $idToDecoderFunc = [];
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

    private $registerMissingSchemas = false;
    private $defaultEncodingFormat = self::MAGIC_BYTE_SCHEMAID;

    public function __construct(CachedSchemaRegistryClient $registry, $options = [])
    {
        $this->registry = $registry;

        if (isset($options['register_missing_schemas'])) {
            $this->registerMissingSchemas = $options['register_missing_schemas'];
        }

        if (isset($options['default_encoding_format'])) {
            $this->defaultEncodingFormat = $options['default_encoding_format'];
        }
    }

    /**
     * Given a parsed avro schema, encode a record for the given topic.
     * The schema is registered with the subject of 'topic-value'
     *
     * @param string     $topic  Topic name
     * @param AvroSchema $schema Avro Schema
     * @param mixed      $record An object to serialize
     * @param bool       $isKey  If the record is a key
     *
     * @return string Encoded record with schema ID as bytes
     */
    public function encodeRecordWithSchema($topic, AvroSchema $schema, $record, $isKey = false, $format = null)
    {
        $suffix = $isKey ? '-key' : '-value';
        $subject = $topic.$suffix;

        $format = $format ?? $this->defaultEncodingFormat;

        switch ($format) {
            case self::MAGIC_BYTE_SUBJECT_VERSION:
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

                return $this->encodeRecordWithSubjectAndVersion($subject, $version, $record, $isKey);
                break;
            case self::MAGIC_BYTE_SCHEMAID:
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

                return $this->encodeRecordWithSchemaId($id, $record, $isKey);
                break;
            default:
                throw new RuntimeException('Unsuported format: '.$format);
        }
    }

    /**
     * Encode a record with a given schema id.
     *
     * @param int   $schemaId
     * @param array $record   A data to serialize
     * @param bool  $isKey    If the record is a key
     *
     * @return AvroIODatumWriter encoder object
     */
    private function encodeRecordWithSchemaId($schemaId, array $record, $isKey = false)
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
     * @param array  $record  A data to serialize
     * @param bool   $isKey   If the record is a key
     *
     * @return AvroIODatumWriter encoder object
     */
    private function encodeRecordWithSubjectAndVersion($subject, $version, $record, $isKey = false)
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

    /**
     * Decode a message from kafka that has been encoded for use with the schema registry.
     *
     * @param string $message
     *
     * @return array
     * @throws \AvroIOException
     */
    public function decodeMessage($message)
    {
        if (strlen($message) < 1) {
            throw new RuntimeException('Message is too small to decode');
        }

        $io = new AvroStringIO($message);

        $magic = unpack('C', $io->read(1));
        $magic = $magic[1];

        switch ($magic) {
            case static::MAGIC_BYTE_SCHEMAID:
                $id = unpack('N', $io->read(4));
                $id = $id[1];

                $decoder = $this->getDecoderById($id);
                break;
            case static::MAGIC_BYTE_SUBJECT_VERSION:
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
                break;
            default:
                return $message;
        }

        return $decoder($io);
    }

    private function getDecoderById($schemaId)
    {
        if (isset($this->idToDecoderFunc[$schemaId])) {
            return $this->idToDecoderFunc[$schemaId];
        }

        $schema = $this->registry->getById($schemaId);

        $reader = new AvroIODatumReader($schema);

        $this->idToDecoderFunc[$schemaId] = function (AvroIO $io) use ($reader) {
            return $reader->read(new AvroIOBinaryDecoder($io));
        };

        return $this->idToDecoderFunc[$schemaId];
    }

    private function getDecoderBySubjectAndVersion($subject, $version)
    {
        if (isset($this->subjectVersionToDecoderFunc[$subject][$version])) {
            return $this->subjectVersionToDecoderFunc[$subject][$version];
        }

        $schema = $this->registry->getBySubjectAndVersion($subject, $version);

        $reader = new AvroIODatumReader($schema);

        if (!isset($this->subjectVersionToDecoderFunc[$subject])) {
            $this->subjectVersionToDecoderFunc[$subject] = [];
        }

        $this->subjectVersionToDecoderFunc[$subject][$version] = function (AvroIO $io) use ($reader) {
            return $reader->read(new AvroIOBinaryDecoder($io));
        };

        return $this->subjectVersionToDecoderFunc[$subject][$version];
    }
}
