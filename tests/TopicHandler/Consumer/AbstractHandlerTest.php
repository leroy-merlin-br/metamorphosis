<?php
namespace Tests\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Tests\LaravelTestCase;

class AbstractHandlerTest extends LaravelTestCase
{
    /** @test */
    public function it_should_handle_warning_consumer()
    {
        $consumerHandler = new class() extends AbstractHandler {
            public function handle(RecordInterface $record): void
            {
            }
        };

        $voidReturn = $consumerHandler->warning(new ResponseWarningException());

        $this->assertNull($voidReturn);
    }

    /** @test */
    public function it_should_handle_failed_consumer()
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
