<?php

namespace Tests\Integration;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Middlewares\AvroSchemaDecoder;
use Metamorphosis\Middlewares\AvroSchemaMixedEncoder;
use Tests\Integration\Dummies\MessageConsumer;
use Tests\Integration\Dummies\MessageProducer;
use Tests\LaravelTestCase;

class ProducerWithAvroTest extends LaravelTestCase
{
    public function testShouldRunAProducerMessagesWithAAvroSchema(): void
    {
        // Given That I
        $this->haveAHandlerConfigured();

        // When I
        $this->haveSomeRandomMessagesProduced();
        $this->expectNotToPerformAssertions();
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
        $saleOrderProducer = app(
            MessageProducer::class,
            ['record' => ['saleOrderId' => 'SALE_ORDER_ID'], 'topic' => 'sale_order']
        );
        $productProducer = app(
            MessageProducer::class,
            ['record' => ['productId' => 'PRODUCT_ID'], 'topic' => 'product']
        );

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

        $saleOrderDispatcher->handle($saleOrderProducer->createRecord());
    }
}
