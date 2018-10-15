<?php
namespace Metamorphosis\Connectors\Producer;

use Metamorphosis\Config\Producer;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
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
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var int Timeout in seconds for the queue when getting messages from the broker for responses
     */
    private $timeoutInSeconds;

    public function setHandler(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function getProducerTopic(Producer $config): ProducerTopic
    {
        $broker = $config->getBrokerConfig();

        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', $broker->getConnections());

        if ($this->canHandleResponse()) {
            $conf->setDrMsgCb(function ($kafka, Message $message) {
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
            $this->timeoutInSeconds = $config->getTimeoutResponse();
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

        $this->queue->poll($this->timeoutInSeconds);
    }

    private function canHandleResponse(): bool
    {
        return $this->handler instanceof HandleableResponseInterface;
    }
}
