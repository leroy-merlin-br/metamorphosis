<?php
namespace Tests\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Tests\LaravelTestCase;

class AbstractHandlerTest extends LaravelTestCase
{
    public function testItShouldHandleWarningConsumer()
    {
        $consumerHandler = new class() extends AbstractHandler {
            public function handle(RecordInterface $record): void
            {
            }
        };

        $voidReturn = $consumerHandler->warning(new ResponseWarningException());

        $this->assertNull($voidReturn);
    }

    public function testItShouldHandleFailedConsumer()
    {
        $consumerHandler = new class() extends AbstractHandler {
            public function handle(RecordInterface $record): void
            {
            }
        };

        $voidReturn = $consumerHandler->failed(new Exception());

        $this->assertNull($voidReturn);
    }
}
