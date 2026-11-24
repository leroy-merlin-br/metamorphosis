<?php

use Rector\Config\RectorConfig;
use Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
use Rector\ValueObject\PhpVersion;

// Only rules that fix PHP 8.4 deprecations/breaking changes,
// keeping compatibility with the minimum supported version (PHP 8.2).

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/config', __DIR__ . '/src', __DIR__ . '/tests'])
    ->withPhpVersion(PhpVersion::PHP_82)
    ->withRules([
        ExplicitNullableParamTypeRector::class,
        AddEscapeArgumentRector::class,
    ]);
