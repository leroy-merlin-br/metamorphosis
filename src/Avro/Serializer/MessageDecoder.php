<?php
namespace Metamorphosis\Avro\Serializer;

use AvroIO;
use AvroIOBinaryDecoder;
use AvroIODatumReader;
use AvroStringIO;
use Closure;
use Metamorphosis\Avro\CachedSchemaRegistryClient;
use RuntimeException;

class MessageDecoder
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
