<?php
namespace Metamorphosis\TopicHandler\Producer;

use Metamorphosis\Record\ProducerRecord;

interface HandlerInterface
{
    /**
     *  An array or string with the payload to be send in a topic.
     *  If an array is passed, it will be json_encoded before send.
     *  If string is passed, it will already be treated as json
     */
    public function getRecord();

    /**
     * The key name for the topic which the record should be send to.
     * This key is the one set inside the config/kafka.php file.
     */
    public function getTopic(): string;

    /**
     * The partition where the record should be to send.
     */
    public function getPartition(): ?int;

    /**
     * The key that defines which partition kafka will put the record.
     * If a key is passed, kafka can guarantee order inside a group of consumers.
     * If no key is passed, kafka cannot guarantee that the record will be delivery
     * in any order, even when inside a same consumer group.
     */
    public function getKey(): ?string;

    public function createRecord(): ProducerRecord;
}
