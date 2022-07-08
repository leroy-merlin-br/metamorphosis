<?php

namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\ProducerConfigManager;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Metamorphosis\TopicHandler\Producer\AbstractProducer;
use Metamorphosis\TopicHandler\Producer\HandleableResponseInterface;
use Metamorphosis\TopicHandler\Producer\HandlerInterface;
use Mockery as m;
use RdKafka\Conf;
use RdKafka\Message;
use RdKafka\Producer as KafkaProducer;
use Tests\LaravelTestCase;

class ConnectorTest extends LaravelTestCase
{
    public function testItShouldMakeSetup(): void
    {
        // Set
        $conf = $this->instance(
            Conf::class,
            m::mock(Conf::class)
        );
        $this->instance(
            KafkaProducer::class,
            m::mock(KafkaProducer::class)
        );
        $configManager = m::mock(ProducerConfigManager::class);
        $configOptions = m::mock(ProducerConfigOptions::class);

        $connector = new Connector();
        $handler = new class ('record', $configOptions) extends AbstractProducer implements HandleableResponseInterface {
            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function success(Message $message): void
            {
            }

            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function failed(Message $message): void
            {
            }
        };

        // Expectations
        $conf->expects()
            ->setDrMsgCb()
            ->withAnyArgs();

        $conf->expects()
            ->set('metadata.broker.list', 'kafka:9092');

        $configManager->expects()
            ->get('connections')
            ->andReturn('kafka:9092');

        $configManager->expects()
            ->get('auth.type')
            ->andReturn('none');

        // Actions
        $result = $connector->getProducerTopic($handler, $configManager);

        // Assertions
        $this->assertInstanceOf(KafkaProducer::class, $result);
    }

    public function testItShouldMakeSetupWithoutHandleResponse(): void
    {
        // Set
        $conf = $this->instance(
            Conf::class,
            m::mock(Conf::class)
        );
        $this->instance(
            KafkaProducer::class,
            m::mock(KafkaProducer::class)
        );
        $configManager = m::mock(ProducerConfigManager::class);
        $configOptions = m::mock(ProducerConfigOptions::class);

        $connector = new Connector();
        $handler = new class ('record', $configOptions) extends AbstractProducer implements HandlerInterface {
            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function success(Message $message): void
            {
            }

            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function failed(Message $message): void
            {
            }
        };

        // Expectations
        $conf->shouldReceive('setDrMsgCb')
            ->never();

        $conf->expects()
            ->set('metadata.broker.list', 'kafka:9092');

        $configManager->expects()
            ->get('connections')
            ->andReturn('kafka:9092');

        $configManager->expects()
            ->get('auth.type')
            ->andReturn('none');

        // Actions
        $result = $connector->getProducerTopic($handler, $configManager);

        // Assertions
        $this->assertInstanceOf(KafkaProducer::class, $result);
    }
}
