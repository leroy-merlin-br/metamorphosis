<?php

namespace Metamorphosis\TopicHandler\Producer;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Record\ProducerRecord;

/**
 * @deprecated deprecated
 */
abstract class AbstractHandler implements HandlerInterface
{
    /**
     * @var mixed
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     */
    protected $record;

    protected string $topic;

    protected string $key;

    protected int $partition;

    public function __construct($record, ?string $topic = null, ?string $key = null, ?int $partition = null)
    {
        $this->record = $record;
        $this->topic = $topic;
        $this->key = $key;
        $this->partition = $partition;
    }

    public function getRecord()
    {
        return $this->record;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getPartition(): ?int
    {
        return $this->partition;
    }

    public function getKey(): ?string
    {
        return $this->key;
    }

    public function createRecord(): ProducerRecord
    {
        $record = $this->getRecord();

        if (is_array($record)) {
            $record = $this->encodeRecord($record);
        }

        $topic = $this->getTopic();
        $partition = $this->getPartition();
        $key = $this->getKey();

        return new ProducerRecord($record, $topic, $partition, $key);
    }

    private function encodeRecord(array $record): string
    {
        $record = json_encode($record, JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException(
                'Cannot convert data into a valid JSON. Reason: ' . json_last_error_msg()
            );
        }

        return $record;
    }
}
