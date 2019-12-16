<?php
namespace Metamorphosis\Connectors\Producer;

use Exception;
use Metamorphosis\Authentication\Factory;
use Metamorphosis\Facades\Manager;
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
    private $timeout;

    public function setHandler(HandlerInterface $handler)
    {
        $this->handler = $handler;
    }

    public function getProducerTopic(): ProducerTopic
    {
        $conf = resolve(Conf::class);

        $conf->set('metadata.broker.list', Manager::get('connections'));

        $this->setCallbackResponses($conf);

        Factory::authenticate($conf);

        $producer = app(KafkaProducer::class, compact('conf'));

        $this->prepareQueueCallbackResponse($producer);

        return $producer->newTopic(Manager::get('topic'));
    }

    public function handleResponsesFromBroker(): void
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        if (!$this->queue) {
            throw new Exception('Cannot handle responses from broker without implementing '.HandleableResponseInterface::class);
        }

        $this->queue->poll($this->timeout);
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

    private function prepareQueueCallbackResponse(KafkaProducer $producer)
    {
        if (!$this->canHandleResponse()) {
            return;
        }

        $this->queue = app(Queue::class, compact('producer'));
        $this->timeout = Manager::get('timeout');
    }

    private function canHandleResponse(): bool
    {
        return $this->handler instanceof HandleableResponseInterface;
    }
}
