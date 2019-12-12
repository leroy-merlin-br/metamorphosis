<?php
namespace Metamorphosis\Avro\Serializer\Decoders;

use AvroStringIO;

interface DecoderInterface
{
    /**
     * Decode an encoded Avro message.
     *
     * @return mixed
     */
    public function decode(AvroStringIO $io);
}
