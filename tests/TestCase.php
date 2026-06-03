<?php

namespace Syriable\Filament\Plugins\Translator\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Syriable\Filament\Plugins\Translator\TranslatorServiceProvider;

class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            TranslatorServiceProvider::class,
        ];
    }
}
