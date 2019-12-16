<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Avro\CachedSchemaRegistryClient;
use Metamorphosis\Avro\Serializer\Decoders\DecoderInterface;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Exceptions\ConfigurationException;
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
        $cachedSchema = new CachedSchemaRegistryClient($this->getOptions());
        $this->decoder = new MessageDecoder($cachedSchema);
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $record->setPayload($this->decoder->decodeMessage($record->getPayload()));

        $handler->handle($record);
    }

    private function getOptions(): array
    {
        if (!$url = Manager::get('url')) {
            throw new ConfigurationException("Avro schema url not found, it's required to use AvroSchemaDecoder Middleware");
        }
        $request_options = Manager::get('request_options') ?: [];

        return compact('url', 'request_options');
    }
}
