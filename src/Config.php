<?php
namespace Metamorphosis;

class Config
{
    protected $topic;

    protected $broker;

    protected $consumerGroupConfig;

    public function __construct(string $topicKey, string $consumerGroup)
    {
        $config = config("kafka.topics.{$topicKey}");

        $this->broker = $config['broker'];
        $this->consumerGroupConfig = [
            'groupName' => $consumerGroup,
            'offset' => $config['topics']['consumer-groups'][$consumerGroup]['offset'],
            'consumer' => $config['topics']['consumer-groups'][$consumerGroup]['consumer'],
        ];

        $this->topic = $config['topic'];
    }

    public function getTopic(): string
    {
        return $this->topic ?? '';
    }

    public function getBroker(): string
    {
        return $this->broker ?? '';
    }

    public function getConsumerGroupSettings(): array
    {
        return $this->consumerGroupConfig;
    }
}
