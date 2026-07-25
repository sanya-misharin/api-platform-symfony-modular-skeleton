<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// Framework-aware rules (Symfony/Doctrine/PHPUnit) live in separate packages
// (rector/rector-symfony, etc.); add them here once installed if desired.
return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/config',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    // PHP version sets are derived from composer.json (php: >=8.4).
    ->withPhpSets()
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
    );
