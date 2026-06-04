<?php

namespace Syriable\Filament\Plugins\Translator\Tests\Fixtures;

use Syriable\Filament\Plugins\Translator\ConventionRegistry;

/**
 * Lightweight stand-in for a Filament action/filter used to assert that
 * {@see ConventionRegistry::prebuiltComponent()}
 * instantiates, runs `setUp()` once, and memoizes the instance.
 */
class PrebuiltStub
{
    public bool $wasSetUp = false;

    public function __construct(public string $name) {}

    protected function setUp(): void
    {
        $this->wasSetUp = true;
    }
}
