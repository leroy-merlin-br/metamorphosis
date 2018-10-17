<?php
namespace Metamorphosis\TopicHandler\Producer;

interface HandlerInterface
{
    /**
     * @param array|string $record    An array or string with the payload to be send in a topic.
     *                                If an array is passed, it will be json_encoded before send.
     *                                If string is passed, it will already be treated as json
     * @param string       $topic     The key name for the topic which the record should be send to.
     *                                This key is the one set inside the config/kafka.php file.
     * @param string|null  $key       The key that defines which partition kafka will put the record.
     *                                If a key is passed, kafka can guarantee order inside a group of consumers.
     *                                If no key is passed, kafka cannot guarantee that the record will be delivery
     *                                in any order, even when inside a same consumer group.
     * @param int|null     $partition The partition where the record should be send
     */
    public function __construct($record, string $topic = null, string $key = null, int $partition = null);

    public function getRecord();

    public function getTopic(): string;

    public function getPartition(): ?int;

    public function getKey(): ?string;
}
