<?php
namespace tests\Facades;

use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Producer;
use Tests\LaravelTestCase;

class MetamorphosisTest extends LaravelTestCase
{
    public function testItShouldFacadeProducer()
    {
        $producer = Metamorphosis::getFacadeRoot();

        $this->assertInstanceOf(Producer::class, $producer);
    }
}
