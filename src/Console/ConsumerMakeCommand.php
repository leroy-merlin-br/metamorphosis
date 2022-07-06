<?php

namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;

class ConsumerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     */
    protected string $name = 'make:kafka-consumer';

    /**
     * The console command description.
     *
     */
    protected string $description = 'Create a new kafka consumer';

    /**
     * The type of class being generated.
     *
     */
    protected string $type = 'KafkaConsumer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/consumer.stub';
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
        return $rootNamespace . '\Kafka\Consumers';
    }
}
