<?php

namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;
use Override;

class ProducerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $name = 'make:kafka-producer';

    /**
     * The console command description.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $description = 'Create a new kafka producer';

    /**
     * The type of class being generated.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $type = 'KafkaProducer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    #[Override]
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
    #[Override]
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Kafka\Producers';
    }
}
