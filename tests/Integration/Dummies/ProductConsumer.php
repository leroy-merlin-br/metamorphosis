<?php
namespace Tests\Integration\Dummies;

use Exception;
use Illuminate\Support\Facades\Log;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Facades\Manager;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;

class ProductConsumer extends AbstractHandler
{
    public function handle(RecordInterface $record): void
    {
        $priceUpdate = $record->getPayload();

        Log::alert($priceUpdate);
    }

    public function warning(ResponseWarningException $exception): void
    {
        Log::debug('Something happened while handling kafka consumer.', [
            'exception' => $exception,
        ]);
    }

    public function failed(Exception $exception): void
    {
        Log::error('Failed to handle kafka record for sku.', [
            'exception' => $exception,
        ]);
    }
}
