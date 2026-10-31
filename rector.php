<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        privatization: true,
        typeDeclarations: true,
        rectorPreset: true,
    )
    // The PHP version comes from composer.json
    ->withPhpSets()
;
