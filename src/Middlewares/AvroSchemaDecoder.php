<?php
namespace Metamorphosis\Middlewares;

use Closure;
use Metamorphosis\Avro\ClientFactory;
use Metamorphosis\Avro\Serializer\Decoders\DecoderInterface;
use Metamorphosis\Avro\Serializer\MessageDecoder;
use Metamorphosis\Exceptions\ConfigurationException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\ConfigOptions\AvroSchema;

class AvroSchemaDecoder implements MiddlewareInterface
{
    /**
     * @var DecoderInterface
     */
    private $decoder;

    /**
     * @var AvroSchema
     */
    private $avroSchema;

    public function __construct(AvroSchema $avroSchema, ClientFactory $factory)
    {
        $this->avroSchema = $avroSchema;
        if (!$this->avroSchema->getUrl()) {
            throw new ConfigurationException("Avro schema url not found, it's required to use AvroSchemaDecoder Middleware");
        }

        $this->decoder = new MessageDecoder($factory->make($avroSchema));
    }

    public function process(RecordInterface $record, Closure $next)
    {
        $record->setPayload($this->decoder->decodeMessage($record->getPayload()));

        return $next($record);
    }
}
