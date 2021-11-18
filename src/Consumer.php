<?php
namespace Metamorphosis;

use Exception;
use Metamorphosis\Connectors\Consumer\Factory;
use Metamorphosis\Consumers\ConsumerInterface;
use Metamorphosis\Exceptions\ResponseTimeoutException;
use Metamorphosis\Exceptions\ResponseWarningException;
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

        $this->consumer = Factory::getConsumer(true, $configManager);
        $this->dispatcher = new Dispatcher($configManager->middlewares());
    }

    public function consume(): ?RecordInterface
    {
        try {
            if ($response = $this->consumer->consume()) {
                $record = app(ConsumerRecord::class, compact('response'));

                return $this->dispatcher->handle($record);
            }
        } catch (ResponseTimeoutException $exception) {
            return null;
        } catch (ResponseWarningException $exception) {
            return null;
        } catch (Exception $exception) {
            return null;
        }

        return null;
    }
}
