<?php
namespace Metamorphosis\Record;

interface RecordInterface
{
    /**
     * Overwrite record payload.
     * It is possible to get the original payload
     * after overwriting it by calling: $record->getOriginal()->payload.
     *
     * @param mixed $payload
     */
    public function setPayload($payload): void;

    /**
     * Get the record payload.
     * It can either be the original value sent to Kafka or
     * a version modified by a middleware.
     *
     * @return mixed
     */
    public function getPayload();

    /**
     * Get the topic name where the record was published.
     *
     * @return string
     */
    public function getTopicName(): string;

    /**
     * Get the partition number where the record was published.
     *
     * @return int|null
     */
    public function getPartition();

    /**
     * Get the record key.
     *
     * @return string|null
     */
    public function getKey();

    /**
     * Get original record when manipulating the topic.
     * With this object, it is possible to get original payload.
     */
    public function getOriginal();
}
