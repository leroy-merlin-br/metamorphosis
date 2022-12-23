<?php

namespace Tests\Unit\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\Record\RecordInterface;
use Metamorphosis\TopicHandler\Consumer\AbstractHandler;
use Tests\LaravelTestCase;

class AbstractHandlerTest extends LaravelTestCase
{
    public function testItShouldHandleWarningConsumer(): void
    {
        // Set
        $consumerHandler = new class () extends AbstractHandler {
            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function handle(RecordInterface $record): void
            {
            }
        };

        // Actions
        $result = $consumerHandler->warning(new ResponseWarningException());

        // Assertions
        $this->assertNull($result);
    }

    public function testItShouldHandleFailedConsumer(): void
    {
        // Set
        $consumerHandler = new class () extends AbstractHandler {
            /** @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter */
            public function handle(RecordInterface $record): void
            {
            }
        };

        // Actions
        $result = $consumerHandler->failed(new Exception());

        // Assertions
        $this->assertNull($result);
    }
}
