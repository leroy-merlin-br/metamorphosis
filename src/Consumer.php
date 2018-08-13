<?php
namespace Metamorphosis;

use RdKafka\Conf;
use RdKafka\KafkaConsumer;

class Consumer
{
    /**
     * @var Conf
     */
    public $conf;

    public $consumerGroup;

    public $offset;

    public $topic;

    public $timeout = 2000000;

    /**
     * @var \Metamorphosis\Contracts\ConsumerTopicHandler
     */
    protected $handler;

    public function __construct(Config $config)
    {
        $this->consumerGroup = $config->getConsumerGroupId();
        $this->offset = $config->getConsumerGroupOffset();
        $this->handler = $config->getConsumerGroupHandler();
        $this->topic = $config->getTopic();

        $connector = new Connector($config->getBrokerConfig());
        $this->conf = $connector->setup();
    }

    public function run(): void
    {
        $kafkaConsumer = $this->getConsumer();

        while(true) {
            $message = $kafkaConsumer->consume($this->timeout);

            if ($message->err) {
                echo 'error: ';
                $this->handleError($message);
                echo "\n";
                continue;
            }

            $this->handler->handle([$message->payload]);
        }
    }

    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    public function setOffset($offset): void
    {
        $this->offset = $offset;
    }

    protected function getConsumer(): KafkaConsumer
    {
        $this->conf->set('group.id', $this->consumerGroup);
        $this->conf->set('auto.offset.reset', $this->offset);

        $consumer = new KafkaConsumer($this->conf);
        $consumer->subscribe([$this->topic]);

        return $consumer;
    }

    protected function handleError($message) {
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                var_dump($message);
                break;
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                echo "No more messages; will wait for more\n";
                break;
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                echo "Timed out\n";
                break;
            default:
                var_dump($message);
                break;
        }
    }
}
