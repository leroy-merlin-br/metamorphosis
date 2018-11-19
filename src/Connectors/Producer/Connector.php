<?php
namespace Metamorphosis\Connectors\Producer;

use Exception;
use Metamorphosis\Config\Producer;
use Metamorphosis\Connectors\AbstractConnector;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use RdKafka\ProducerTopic;

class Connector extends AbstractConnector
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
        $conf = $this->getDefaultConf($config);

        $this->setCallbackResponses($conf);

        $producer = app(KafkaProducer::class, compact('conf'));

        $this->prepareQueueCallbackResponse($config, $producer);

        return $producer->newTopic($config->getTopic());
    }

    public function handleResponsesFromBroker(): void
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        if (!$this->queue) {
            throw new Exception('Cannot handle responses from broker without implementing '.HandleableResponseInterface::class);
        }

        $this->queue->poll($this->timeoutInSeconds);
    }

    private function setCallbackResponses(Conf $conf)
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        $conf->setDrMsgCb(function ($kafka, Message $message) {
            if ($message->err) {
                $this->handler->failed($message);
            } else {
                $this->handler->success($message);
            }
        });
    }

    private function prepareQueueCallbackResponse(Producer $config, KafkaProducer $producer)
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        $this->queue = app(Queue::class, compact('producer'));
        $this->timeoutInSeconds = $config->getTimeoutResponse();
    }

    private function canHandleResponse(): bool
    {
        return $this->handler instanceof HandleableResponseInterface;
    }
}
