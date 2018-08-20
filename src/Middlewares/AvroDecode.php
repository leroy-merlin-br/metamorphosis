<?php
namespace Metamorphosis\Middlewares;

use Avro\DataIO\DataIOReader;
use Avro\Datum\IODatumReader;
use Avro\IO\StringIO;
use Metamorphosis\Record;
use Metamorphosis\Middlewares\Handler\MiddlewareHandler;

class AvroDecode implements Middleware
{
    public function process(Record $record, MiddlewareHandler $handler): void
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
