<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\Decoders\DecoderInterface;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\RecordInterface;

class AvroSchemaDecoder implements MiddlewareInterface
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct()
    {
        $options = [
            'url' => Manager::get('url'),
            'timeout' => Manager::get('timeout'),
            'authorization' => Manager::get('authorization.type'),
            'username' => Manager::get('authorization.username'),
            'password' => Manager::get('authorization.password'),
        ];

        $cachedSchema = new CachedSchemaRegistryClient($options);

        $this->decoder = new MessageDecoder($cachedSchema);
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $record->setPayload($this->decoder->decodeMessage($record->getPayload()));

        $handler->handle($record);
    }
}
