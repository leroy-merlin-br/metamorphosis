<?php
namespace tests\Facades;

use Metamorphosis\Facades\Metamorphosis;
use Metamorphosis\Producer;
use Tests\LaravelTestCase;

class MetamorphosisTest extends LaravelTestCase
{
    /** @test */
    public function it_should_facade_producer()
    {
        $producer = Metamorphosis::getFacadeRoot();

        $this->assertInstanceOf(Producer::class, $producer);
    }
}
