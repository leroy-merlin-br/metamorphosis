<?php
namespace Metamorphosis;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class Consumer
{
    /**
     * @var Connector $connector
     */
    public $connector;

    /**
     * @var Conf $conf
     */
    public $conf;

    public function __construct(string $topicKey, string $consumerGroup)
    {
        $config = new Config($topicKey,$consumerGroup);

        $consumerGroupConfig = $config->getConsumerGroupSettings();

        $this->consumerGroup = $consumerGroupConfig['groupName'];
        $this->offset = $consumerGroupConfig['offset'];
        $this->consumer = $consumerGroupConfig['consumer'];

        $this->topic = $config->getTopic();

        $this->connector = new Connector($config->getBroker());
        $this->conf = $this->connector->setup();
    }

    public function consume()
    {
        $kafkaConsumer = $this->getConsumer();
        $timeout = 2000 * 1000;

        $consumer = $this->consumer;

        while(true) {
            $message = $kafkaConsumer->consume($timeout);

            if ($message->err) {
                echo 'error: ';
                handleError($message);
                echo "\n";
                continue;
            }

            $consumer([$message->payload]);
        }
    }

    protected function getConsumer(): KafkaConsumer
    {
        $consumerGroup = key($this->consumerConfs['consumer-groups']);
        $this->conf->set('group.id', $consumerGroup);
        $this->conf->set('auto.offset.reset', $this->consumerConfs['consumer-groups']['offset']);

        $consumer = new KafkaConsumer($this->conf);
        $consumer->subscribe([$this->topic]);

        return $consumer;
    }
}
