<?php
namespace Tests;

use Metamorphosis\MetamorphosisServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelTestCase extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [MetamorphosisServiceProvider::class];
    }
}
