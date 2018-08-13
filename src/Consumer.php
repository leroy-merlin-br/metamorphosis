<?php
namespace Metamorphosis;

use Metamorphosis\Middlewares\Dispatcher;
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
        $dispatcher = new Dispatcher($config);

        $kafkaConsumer = $this->getConsumer();

        while (true) {
            $originalMessage = $kafkaConsumer->consume($this->timeout);

            $message = new Message($originalMessage);

            $dispatcher->handle($message);
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
}
