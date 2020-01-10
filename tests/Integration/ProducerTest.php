<?php
namespace Tests\Integration;

use Exception;
use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Tests\Integration\Dummies\ProductConsumer;
use Tests\Integration\Dummies\ProductHasChanged;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->withoutAuthentication();
    }

    public function testShouldRunAProducerAndReceiveMessagesWithAHighLevelConsumer(): void
    {
        // Given That I
        $this->haveAConsumerHandlerConfigured();
        $recordMessage = $this->haveSomeRandomMessagesProduced();

        // I Expect That
        $this->myMessagesHaveBeenLogged($recordMessage);

        // When I
        $this->runTheConsumer();
    }

    public function testShouldRunAProducerAndReceiveMessagesWithALowLevelConsumer(): void
    {
        // Given That I
        $firstMessage = 'First Message';
        $secondMessage = 'Second Message';

        $this->haveALowLevelConsumerConfigured();
        $this->haveTwoMessagesProducedBefore($firstMessage);
        $this->andHaveMoreTwoMessagesProducedLater($secondMessage);

        // I Expect That
        $this->mySecondMessageHaveBeenLogged($secondMessage);

        // When I
        $this->runTheLowLevelConsumer();
    }

    protected function withoutAuthentication(): void
    {
        config(['kafka.brokers.default.auth' => []]);
    }

    protected function haveAConsumerHandlerConfigured(): void
    {
        config(['kafka.topics.default.consumer_groups.test-consumer-group.handler' => ProductConsumer::class]);
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
                        'consumer_groups' => [
                            'test-consumer-group' => [
                                'offset_reset' => 'earliest',
                                'offset' => 0,
                                'handler' => ProductConsumer::class,
                                'timeout' => 20000,
                                'middlewares' => [],
                            ],
                        ],
                        'required_acknowledgment' => true,
                        'is_async' => true,
                        'max_poll_records' => 500,
                        'flush_attempts' => 10,
                        'middlewares' => [],
                        'timeout' => 20000,
                    ]
                ]
            ]
        );
    }

    protected function runTheLowLevelConsumer(): void
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

    private function haveSomeRandomMessagesProduced(): string
    {
        $record = str_random(10);
        $producer = app(ProductHasChanged::class, compact('record'));

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);

        return $record;
    }

    private function myMessagesHaveBeenLogged(string $recordMessage): void
    {
        Log::shouldReceive('info')
            ->withAnyArgs();

        Log::shouldReceive('alert')
            ->with($recordMessage)
            ->twice();
    }

    private function haveTwoMessagesProducedBefore(string $recordMessage): void
    {
        $this->produceRecordMessage($recordMessage);
    }

    private function andHaveMoreTwoMessagesProducedLater(string $recordMessage): void
    {
        $this->produceRecordMessage($recordMessage);
    }

    private function produceRecordMessage(string $record): string
    {
        $producer = app(ProductHasChanged::class, compact('record'));
        $producer->topic = 'low_level';

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);

        return $record;
    }

    private function mySecondMessageHaveBeenLogged(string $secondMessage): void
    {
        $this->myMessagesHaveBeenLogged($secondMessage);
    }
}
