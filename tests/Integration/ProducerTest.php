<?php

namespace Tests\Integration;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Metamorphosis\Facades\Metamorphosis;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    protected string $highLevelMessage;

    protected string $firstLowLevelMessage;

    protected string $secondLowLevelMessage;

    public function testShouldRunAProducerAndReceiveMessagesWithAHighLevelConsumer(): void
    {
        // Given That I
        $this->haveAConsumerHandlerConfigured();
        $this->haveNoPartitionConfigured();
        $this->haveSomeRandomMessagesProduced();

        // I Expect That
        $this->myMessagesHaveBeenLogged();

        // When I
        $this->runTheConsumer();
    }

    public function testShouldRunAProducerAndReceiveMessagesWithALowLevelConsumer(): void
    {
        // Given That I
        $this->haveSomeMessagesToBeSent();
        $this->haveALowLevelConsumerConfigured();
        $this->haveFourProducedMessages();

        // I Expect That
        $this->messageThreeAndFourAreConsumed();

        // When I
        $this->runTheLowLevelConsumerSkippingTheFirstTwoMessagesAndLimitingToTwoMessagesConsumed();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutAuthentication();
    }

    protected function withoutAuthentication(): void
    {
        config(['kafka.brokers.default.auth' => []]);
    }

    protected function haveAConsumerHandlerConfigured(): void
    {
        config(
            ['kafka.topics.default.consumer.consumer_groups.test-consumer-group.handler' => MessageConsumer::class]
        );
    }

    protected function runTheConsumer(): void
    {
        $this->artisan(
            'kafka:consume',
            [
                'topic' => 'default',
                'consumer_group' => 'test-consumer-group',
                '--timeout' => 20000,
                '--times' => 2,
            ]
        );
    }

    protected function haveALowLevelConsumerConfigured(): void
    {
        config(
            [
                'kafka.topics' => [
                    'low_level' => [
                        'topic_id' => 'low_level',
                        'broker' => 'default',
                        'consumer' => [
                            'consumer_groups' => [
                                'test-consumer-group' => [
                                    'offset_reset' => 'earliest',
                                    'offset' => 0,
                                    'handler' => MessageConsumer::class,
                                    'timeout' => 20000,
                                    'middlewares' => [],
                                ],
                            ],
                        ],
                        'producer' => [
                            'required_acknowledgment' => true,
                            'is_async' => true,
                            'max_poll_records' => 500,
                            'flush_attempts' => 10,
                            'middlewares' => [],
                            'timeout' => 20000,
                        ],
                    ],
                ],
            ]
        );
    }

    protected function runTheLowLevelConsumerSkippingTheFirstTwoMessagesAndLimitingToTwoMessagesConsumed(): void
    {
        $this->artisan(
            'kafka:consume',
            [
                'topic' => 'low_level',
                'consumer_group' => 'test-consumer-group',
                '--partition' => 0,
                '--offset' => 2,
                '--timeout' => 20000,
                '--times' => 2,
            ]
        );
    }

    private function haveSomeRandomMessagesProduced(): void
    {
        $this->highLevelMessage = Str::random(10);
        $producer = app(
            MessageProducer::class,
            [
                'record' => $this->highLevelMessage,
                'topic' => 'default',
            ]
        );

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);
    }

    private function produceRecordMessage(string $record): string
    {
        $topic = 'low_level';
        $producer = app(MessageProducer::class, compact('record', 'topic'));

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);

        return $record;
    }

    private function messageThreeAndFourAreConsumed(): void
    {
        $this->setLogExpectationsFor($this->secondLowLevelMessage);
    }

    private function myMessagesHaveBeenLogged(): void
    {
        $this->setLogExpectationsFor($this->highLevelMessage);
    }

    private function setLogExpectationsFor(string $message): void
    {
        Log::shouldReceive('info')
            ->withAnyArgs();

        Log::shouldReceive('alert')
            ->with($message)
            ->twice();
    }

    private function haveSomeMessagesToBeSent()
    {
        $this->firstLowLevelMessage = 'First Message';
        $this->secondLowLevelMessage = 'Second Message';
    }

    private function haveFourProducedMessages(): void
    {
        $this->produceRecordMessage($this->firstLowLevelMessage);
        $this->produceRecordMessage($this->secondLowLevelMessage);
    }

    private function haveNoPartitionConfigured(): void
    {
        config(
            ['kafka.topics.default.consumer.consumer_groups.test-consumer-group.partition' => -1]
        );
    }
}
