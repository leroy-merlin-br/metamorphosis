<?php
namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;

class MiddlewareMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:kafka-middleware';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new kafka middleware';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'KafkaMiddleware';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__.'/stubs/middleware.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Kafka\Middlewares';
    }
}
