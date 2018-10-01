<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Config\Producer;
use Metamorphosis\TopicHandler\Producer\HandleableResponse;
use Metamorphosis\TopicHandler\Producer\Handler;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Connector
{
    /**
     * @var Queue
     */
    public $queue;

    /**
     * @var Handler
     */
    private $handler;

    public function setHandler(Handler $handler)
    {
        $this->handler = $handler;
    }

    public function getProducerTopic(Producer $config): ProducerTopic
    {
        $broker = $config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        if ($this->canHandleResponse()) {
            $conf->setDrMsgCb(function($kafka, Message $message) {
                if ($message->err) {
                    $this->handler->failed($message);
                } else {
                    $this->handler->success($message);
                }
            });
        }

        $broker->authenticate($conf);

        $producer = app(KafkaProducer::class, ['conf' => $conf]);

        if ($this->canHandleResponse()) {
            $this->queue = app(Queue::class, ['producer' => $producer]);
        }

        return $producer->newTopic($config->getTopic());
    }

    public function handleResponsesFromBroker(): void
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        if (!$this->queue) {
            throw new \Exception('this should not happen at all');
        }

        $timeoutInSeconds = 50;
        $this->queue->poll($timeoutInSeconds);
    }

    private function canHandleResponse(): bool
    {
        return $this->handler instanceof HandleableResponse;
    }
}
