<?php
namespace Metamorphosis\TopicHandler\Producer;

use Metamorphosis\Exceptions\JsonException;
use Metamorphosis\Record\ProducerRecord;
use Metamorphosis\TopicHandler\ConfigOptions;

class AbstractProducer implements HandlerInterface
{
    /**
     * @var mixed
     */
    protected $record;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var ConfigOptions
     */
    private $configOptions;

    public function __construct($record, ConfigOptions $configOptions, string $key = null)
    {
        $this->record = $record;
        $this->key = $key;
        $this->configOptions = $configOptions;
    }

    public function getConfigOptions(): ConfigOptions
    {
        return $this->configOptions;
    }

    public function getRecord()
    {
        return $this->record;
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

        $topic = $this->getConfigOptions()->getTopicId();
        $partition = $this->getConfigOptions()->getPartition();
        $key = $this->getKey();

        return new ProducerRecord($record, $topic, $partition, $key);
    }

    private function encodeRecord(array $record): string
    {
        $record = json_encode($record, JSON_PRESERVE_ZERO_FRACTION);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JsonException('Cannot convert data into a valid JSON. Reason: '.json_last_error_msg());
        }

        return $record;
    }
}
