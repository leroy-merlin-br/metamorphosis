<?php

namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;
use Override;

class ConsumerMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     * @string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $name = 'make:kafka-consumer';

    /**
     * The console command description.
     * @string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $description = 'Create a new kafka consumer';

    /**
     * The type of class being generated.
     * @string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $type = 'KafkaConsumer';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    #[Override]
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
    #[Override]
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Kafka\Consumers';
    }
}
