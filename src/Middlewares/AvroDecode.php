<?php
namespace Metamorphosis\Middlewares;

use Avro\DataIO\DataIOReader;
use Avro\Datum\IODatumReader;
use Avro\IO\StringIO;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\Middlewares\Handler\MiddlewareHandlerInterface;

class AvroDecode implements MiddlewareInterface
{
    public function process(RecordInterface $record, MiddlewareHandlerInterface $handler): void
    {
        $readIo = new StringIO($record->getPayload());
        $dataReader = new DataIOReader($readIo, new IODatumReader());

        $records = [];
        foreach ($dataReader->data() as $dataRecord) {
            $records[] = $dataRecord;
        }

        $record->setPayload($records);

        $handler->handle($record);
    }
}
