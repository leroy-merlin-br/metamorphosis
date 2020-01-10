<?php
namespace Tests\Integration;

use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducer;
use Tests\LaravelTestCase;

class ProducerTest extends LaravelTestCase
{
    /**
     * @var string
     */
    protected $highLevelMessage;

    /**
     * @var string
     */
    protected $firstLowLevelMessage;

    /**
     * @var string
     */
    protected $secondLowLevelMessage;

    protected function setUp()
    {
        parent::setUp();
        $this->withoutAuthentication();
    }

    public function testShouldRunAProducerAndReceiveMessagesWithAHighLevelConsumer(): void
    {
        // Given That I
        $this->haveAConsumerHandlerConfigured();
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
        $this->haveTwoMessagesProducedBefore();
        $this->andHaveMoreTwoMessagesProducedLater();

        // I Expect That
        $this->mySecondMessageHaveBeenLogged();

        // When I
        $this->runTheLowLevelConsumer();
    }

    protected function withoutAuthentication(): void
    {
        config(['kafka.brokers.default.auth' => []]);
    }

    protected function haveAConsumerHandlerConfigured(): void
    {
        config(['kafka.topics.default.consumer_groups.test-consumer-group.handler' => MessageConsumer::class]);
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
                                'handler' => MessageConsumer::class,
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
                    ],
                ],
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

    private function haveSomeRandomMessagesProduced(): void
    {
        $this->highLevelMessage = str_random(10);
        $producer = app(MessageProducer::class, ['record' => $this->highLevelMessage]);

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);
    }

    private function haveTwoMessagesProducedBefore(): void
    {
        $this->produceRecordMessage($this->firstLowLevelMessage);
    }

    private function andHaveMoreTwoMessagesProducedLater(): void
    {
        $this->produceRecordMessage($this->secondLowLevelMessage);
    }

    private function produceRecordMessage(string $record): string
    {
        $producer = app(MessageProducer::class, compact('record'));
        $producer->topic = 'low_level';

        Metamorphosis::produce($producer);
        Metamorphosis::produce($producer);

        return $record;
    }

    private function mySecondMessageHaveBeenLogged(): void
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
}
