<?php

use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    $rectorConfig->sets([
        SetList::CODE_QUALITY,
    ]);

    $rectorConfig->phpVersion(PhpVersion::PHP_74);
};
