<?php

namespace Metamorphosis\TopicHandler\Consumer;

use Exception;
use Metamorphosis\Exceptions\ResponseWarningException;
use Metamorphosis\TopicHandler\ConfigOptions\Consumer as ConsumerConfigOptions;
use Override;

abstract class AbstractHandler implements Handler
{
    /**
     * Merge and override config from kafka file.
     *
     */
    private ?ConsumerConfigOptions $configOptions = null;

    public function __construct(?ConsumerConfigOptions $configOptions = null)
    {
        $this->configOptions = $configOptions;
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function warning(ResponseWarningException $exception): void
    {
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    public function failed(Exception $exception): void
    {
    }

    #[Override]
    public function finished(): void
    {
    }

    public function getConfigOptions(): ?ConsumerConfigOptions
    {
        return $this->configOptions;
    }
}
