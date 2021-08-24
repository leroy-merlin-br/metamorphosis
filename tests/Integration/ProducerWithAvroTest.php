<?php
namespace Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Middlewares\AvroSchemaDecoder;
use Metamorphosis\Middlewares\AvroSchemaMixedEncoder;
use Mockery as m;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducer;
use Tests\LaravelTestCase;

class ProducerWithAvroTest extends LaravelTestCase
{
    /**
     * @var string
     */
    protected $highLevelMessage;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutAuthentication();
    }

    public function testShouldRunAProducerAndReceiveMessagesWithAHighLevelConsumer(): void
    {
        // Given That I
        $this->haveAHandlerConfigured();
        $this->haveSomeRandomMessagesProduced();

        // When I
        $this->runTheConsumer();
    }

    protected function withoutAuthentication(): void
    {
        config(['kafka.brokers.default.auth' => []]);
    }

    protected function haveAHandlerConfigured(): void
    {
        config([
            'kafka' => [
                'brokers' => [
                    'test' => [
                        'connections' => 'kafka:9092',
                    ],
                ],
                'topics' => [
                    'sale_order' => [
                        'broker' => 'test',
                        'consumer' => [
                            'consumer_groups' => [
                                'test' => [
                            'handler' => MessageConsumer::class,
                            'middlewares' => [
                                AvroSchemaDecoder::class,
                            ],
                        ],
                        ],
                        ],
                        'producer' => [
                            'middlewares' => [
                                AvroSchemaMixedEncoder::class,
                            ],
                        ],
                    ],
                    'product' => [
                        'broker' => 'test',
                        'consumer' => [
                            'consumer_groups' => [
                                'test' => [
                            'handler' => MessageConsumer::class,
                            'middlewares' => [
                                AvroSchemaDecoder::class,
                            ],
                        ],
                        ]
                        ],
                        'producer' => [
                            'middlewares' => [
                                AvroSchemaMixedEncoder::class,
                            ],
                        ],
                    ],
                ],
                'avro_schemas' => [
                    'sale_order' => ['url' => 'http://schema-registry'],
                    'product' => ['url' => 'http://schema-registry'],
                ]
            ]
        ]);
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

    private function haveSomeRandomMessagesProduced(): void
    {
        $saleOrderProducer = app(MessageProducer::class, ['record' => ['saleOrderId' => 'SALE_ORDER_ID'], 'topic' => 'sale_order']);
        $productProducer = app(MessageProducer::class, ['record' => ['productId' => 'PRODUCT_ID'],  'topic' => 'product']);

        $saleOrderSchemaResponse = '{"type":"record","name":"sale_oder","fields":[{"name":"saleOrderId","type":["string","null"],"default":null}]}';
        $productSchemaResponse = '{"type":"record","name":"product","fields":[{"name":"productId","type":["string","null"],"default":null}]}';

        $mockedHandler = new MockHandler([
            new Response(200, [], $saleOrderSchemaResponse),
            new Response(200, [], $productSchemaResponse),
        ]);
        $handlerStack = HandlerStack::create($mockedHandler);
        $client = new GuzzleClient(['handler' => $handlerStack]);
        $client = m::mock(GuzzleClient::class);
        $this->instance(GuzzleClient::class, $client);

        $saleOrderDispatcher = Metamorphosis::build($saleOrderProducer);
        $productDispatcher = Metamorphosis::build($productProducer);

        $saleOrderDispatcher->handle($saleOrderProducer->createRecord());
        $productDispatcher->handle($saleOrderProducer->createRecord());
        $saleOrderDispatcher->handle($saleOrderProducer->createRecord());
    }
}
