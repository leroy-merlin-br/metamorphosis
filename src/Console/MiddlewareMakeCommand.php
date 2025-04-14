<?php

namespace Metamorphosis\Console;

use Illuminate\Console\GeneratorCommand;
use Override;

class MiddlewareMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $name = 'make:kafka-middleware';

    /**
     * The console command description.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $description = 'Create a new kafka middleware';

    /**
     * The type of class being generated.
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
     */
    protected $type = 'KafkaMiddleware';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    #[Override]
    protected function getStub()
    {
        return __DIR__ . '/stubs/middleware.stub';
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
        return $rootNamespace . '\Kafka\Middlewares';
    }
}
