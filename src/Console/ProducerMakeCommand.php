<?php

namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;

class ProducerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     */
    protected string $name = 'make:kafka-producer';

    /**
     * The console command description.
     *
     */
    protected string $description = 'Create a new kafka producer';

    /**
     * The type of class being generated.
     *
     */
    protected string $type = 'KafkaProducer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/producer.stub';
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
        return $rootNamespace . '\Kafka\Producers';
    }
}
