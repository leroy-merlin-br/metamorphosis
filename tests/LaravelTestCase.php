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

    protected function instance($abstract, $instance)
    {
        $this->app->bind(
            $abstract,
            function () use ($instance) {
                return $instance;
            }
        );

        return $instance;
    }
}
