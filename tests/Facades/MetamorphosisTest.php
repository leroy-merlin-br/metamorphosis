<?php
namespace tests\Facades;

use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Producer;
use Tests\LaravelTestCase;

class MetamorphosisTest extends LaravelTestCase
{
    public function testItShouldFacadeProducer(): void
    {
        // Actions
        $producer = Metamorphosis::getFacadeRoot();

        // Assertions
        $this->assertInstanceOf(Producer::class, $producer);
    }
}
