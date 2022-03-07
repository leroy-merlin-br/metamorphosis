<?php
namespace Metamorphosis;

use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Middlewares\Handler\Dispatcher;
use Metamorphosis\Record\ConsumerRecord;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;

class Consumer
{
    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    public function __construct(ConsumerConfigManager $configManager, ConsumerConfigOptions $configOptions)
    {
        $configManager->set($configOptions->toArray());

        $this->consumer = Factory::getConsumer(true, $configOptions);
        $this->dispatcher = new Dispatcher($configOptions->getMiddlewares());
    }

    public function consume(): ?RecordInterface
    {
        if ($response = $this->consumer->consume()) {
            $record = app(ConsumerRecord::class, compact('response'));

            return $this->dispatcher->handle($record);
        }

        return null;
    }
}
