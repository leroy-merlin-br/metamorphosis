<?php

namespace Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Log;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Middlewares\AvroSchemaDecoder;
use Metamorphosis\Middlewares\AvroSchemaMixedEncoder;
use Metamorphosis\TopicHandler\ConfigOptions\Auth\None;
use Metamorphosis\TopicHandler\ConfigOptions\Broker;
use Metamorphosis\TopicHandler\ConfigOptions\Producer as ProducerConfigOptions;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducer;
use Tests\LaravelTestCase;

class ProducerWithAvroTest extends LaravelTestCase
{
    /**
     * @var string[]
     */
    protected $records;

    public function setUp(): void
    {
        parent::setUp();
        $this->records = ['saleOrderId' => 'SALE_ORDER_ID', 'productId' => 'PRODUCT_ID'];
    }

    public function testShouldRunAProducerMessagesWithAAvroSchema(): void
    {
        // Given That I
        $this->haveAHandlerConfigured();

        // When I
        $this->haveSomeRandomMessagesProduced();

        // I expect that
        $this->myMessagesHaveBeenLogged();
    }

    protected function haveAHandlerConfigured(): void
    {
        config([
            'kafka' => [
                'brokers' => [
                    'test' => [
                        'connections' => env(
                            'KAFKA_BROKER_CONNECTIONS',
                            'kafka:9092'
                        ),
                    ],
                ],
                'topics' => [
                    'sale_order' => [
                        'broker' => 'test',
                        'topic_id' => 'sale_order',
                        'consumer' => [
                            'consumer_groups' => [
                                'test' => [
                                    'auto_commit' => false,
                                    'offset_reset' => 'smallest',
                                    'offset' => 0,
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
                        'topic_id' => 'product',
                        'consumer' => [
                            'consumer_groups' => [
                                'test' => [
                                    'auto_commit' => false,
                                    'offset_reset' => 'smallest',
                                    'offset' => 0,
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
                ],
                'avro_schemas' => [
                    'sale_order' => ['url' => 'http://schema-registry/'],
                    'product' => ['url' => 'http://schema-registry/'],
                ],
            ],
        ]);
    }

    private function haveSomeRandomMessagesProduced(): void
    {
        $producerConfigOptionsSale = $this->createProducerConfigOptions('sale_order');
        $producerConfigOptionsProduct = $this->createProducerConfigOptions('product');

        $saleOrderProducer = app(MessageProducer::class, [
            'record' => ['saleOrderId' => 'SALE_ORDER_ID'],
            'producer' => $producerConfigOptionsSale,
        ]);
        $productProducer = app(MessageProducer::class, [
            'record' => ['productId' => 'PRODUCT_ID'],
            'producer' => $producerConfigOptionsProduct,
        ]);

        $saleOrderSchemaResponse = '{
           "subject":"sale_order-value",
           "version":1,
           "id":1,
           "schema":"{\"type\":\"record\",\"name\":\"sale_order\",\"fields\":[{\"name\":\"saleOrderId\",\"type\":[\"string\",\"null\"],\"default\":null}]}"
        }';

        $productSchemaResponse = '{
           "subject":"product-value",
           "version":1,
           "id":2,
           "schema":"{\"type\":\"record\",\"name\":\"product\",\"fields\":[{\"name\":\"productId\",\"type\":[\"string\",\"null\"],\"default\":null}]}"
        }';

        $mockedHandler = new MockHandler([
            new Response(200, [], $saleOrderSchemaResponse),
            new Response(200, [], $productSchemaResponse),
        ]);
        $handlerStack = HandlerStack::create($mockedHandler);
        $client = new GuzzleClient(['handler' => $handlerStack]);
        $this->instance(GuzzleClient::class, $client);

        $saleOrderDispatcher = Metamorphosis::build($saleOrderProducer);
        $saleOrderDispatcher->handle($saleOrderProducer->createRecord());

        $productDispatcher = Metamorphosis::build($productProducer);
        $productDispatcher->handle($productProducer->createRecord());
    }

    private function myMessagesHaveBeenLogged(): void
    {
        $this->setLogExpectationsFor($this->records['saleOrderId']);
        $this->setLogExpectationsFor($this->records['productId']);
    }

    private function setLogExpectationsFor(string $message): void
    {
        Log::shouldReceive('info')
            ->with($message);
    }

    private function createProducerConfigOptions(string $topicId): ProducerConfigOptions
    {
        $broker = new Broker('kafka:9092', new None());
        return new ProducerConfigOptions(
            $topicId,
            $broker,
            null,
            null,
            [],
            2000,
            false,
            true
        );
    }
}
