<?php

declare(strict_types=1);

/*
 * Make the PHPUnit classes visible to PHPStan.
 *
 * Tests run through symfony/phpunit-bridge (`vendor/bin/simple-phpunit`), which
 * installs PHPUnit under vendor/bin/.phpunit/ instead of the project's autoloader.
 * Register that install so PHPStan can resolve PHPUnit\Framework\TestCase when it
 * analyses tests/. Run `vendor/bin/simple-phpunit install` once before PHPStan on
 * a fresh checkout so this directory exists.
 */
$autoloaders = glob(__DIR__.'/vendor/bin/.phpunit/phpunit-*/vendor/autoload.php') ?: [];

foreach ($autoloaders as $autoloader) {
    require_once $autoloader;
}
