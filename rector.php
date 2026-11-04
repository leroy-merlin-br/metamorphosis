<?php

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSets([SetList::CODE_QUALITY])
    ->withPhpVersion(PhpVersion::PHP_83);
