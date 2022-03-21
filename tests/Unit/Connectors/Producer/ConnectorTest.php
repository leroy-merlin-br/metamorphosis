<?php

namespace Tests\Unit\Connectors\Producer;

use Metamorphosis\Connectors\Producer\Connector;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
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

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = m::mock(ProducerConfigOptions::class);

        $connector = new Connector();
        $handler = new class('record', $producerConfigOptions) extends AbstractProducer implements HandleableResponseInterface {
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
            ->set('metadata.broker.list', 0);

        $producerConfigOptions->expects()
            ->getBroker()
            ->andReturn($broker);

        // Actions
        $result = $connector->getProducerTopic($handler, $producerConfigOptions);

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

        $broker = new Broker('kafka:9092', new None());
        $producerConfigOptions = m::mock(ProducerConfigOptions::class);

        $connector = new Connector();
        $handler = new class('record', $producerConfigOptions) extends AbstractProducer implements HandlerInterface {
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
            ->set('metadata.broker.list', 0);

        $producerConfigOptions->expects()
            ->getBroker()
            ->andReturn($broker);

        // Actions
        $result = $connector->getProducerTopic($handler, $producerConfigOptions);

        // Assertions
        $this->assertInstanceOf(KafkaProducer::class, $result);
    }
}
