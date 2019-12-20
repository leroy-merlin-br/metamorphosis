<?php
namespace Metamorphosis\Middlewares\Handler;

use Kafka\ProducerConfig;
use Metamorphosis\Connectors\Producer\Config;
use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\MiddlewareInterface;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;

class Producer implements MiddlewareInterface
{
    /**
     * @var Connector
     */
    private $connector;

    /**
     * @var HandlerInterface
     */
    private $producerHandler;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Connector $connector, Config $config)
    {
        $this->connector = $connector;
        $this->config = $config;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $this->config->setOption($record->getTopicName());

        $config = ProducerConfig::getInstance();
        $config->setMetadataRefreshIntervalMs(10000);
        $config->setMetadataBrokerList(Manager::get('connections'));
        $config->setBrokerVersion('1.0.0');
        $config->setRequiredAck(1);
        $config->setIsAsyn(Manager::get('isASync'));
        $config->setProduceInterval(500);

        $producer = new \Kafka\Producer(
            function() use ($record) {
                return [
                    [
                        'topic' => Manager::get('topic'),
                        'value' => $record->getPayload(),
                        'key' => $record->getKey(),
                    ],
                ];
            }
        );

        if ($this->canHandleResponse($this->producerHandler) ) {
            $producer->success(function ($result) {
                $this->producerHandler->success($result);
            });

            $producer->error(function ($errorCode) {
                $this->producerHandler->failed($errorCode);
            });
        }

        $producer->send(true);
    }

    public function setProducerHandler(HandlerInterface $handler)
    {
        $this->producerHandler = $handler;
    }

    private function canHandleResponse($handler): bool
    {
        return $handler instanceof HandleableResponseInterface;
    }
}
