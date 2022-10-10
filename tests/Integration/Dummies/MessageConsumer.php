<?php
namespace Tests\Integration\Dummies;

use Illuminate\Support\Facades\Log;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Throwable;

class MessageConsumer extends AbstractHandler
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

    public function failed(Throwable $throwable): void
    {
        Log::error('Failed to handle kafka record for sku.', [
            'exception' => $throwable,
        ]);
    }
}
