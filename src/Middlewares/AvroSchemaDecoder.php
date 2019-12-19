<?php
namespace Metamorphosis\Middlewares;

use Metamorphosis\Avro\Serializer\Decoders\DecoderInterface;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;
use Metamorphosis\Record\RecordInterface;

class AvroSchemaDecoder implements MiddlewareInterface
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    public function __construct(MessageDecoder $decoder)
    {
        $this->decoder = $decoder;
    }

    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $record->setPayload($this->decoder->decodeMessage($record->getPayload()));

        $handler->handle($record);
    }
}
