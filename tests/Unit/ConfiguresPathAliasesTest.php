<?php

use Syriable\Filament\Plugins\Translator\Plugin\ConfiguresPathAliases;

function makePathAliasHolder(): object
{
    return new class
    {
        use ConfiguresPathAliases;
    };
}

it('merges path aliases by default', function () {
    $holder = makePathAliasHolder();

    $holder->pathAliases(['App\\Livewire' => 'livewire']);
    $holder->pathAliases(['App\\Filament' => 'filament']);

    expect($holder->getPathAliases())->toBe([
        'App\\Livewire' => 'livewire',
        'App\\Filament' => 'filament',
    ]);
});

it('replaces path aliases when merge is disabled', function () {
    $holder = makePathAliasHolder();

    $holder->pathAliases(['App\\Livewire' => 'livewire']);
    $holder->pathAliases(['App\\Filament' => 'filament'], merge: false);

    expect($holder->getPathAliases())->toBe([
        'App\\Filament' => 'filament',
    ]);
});

it('returns an empty array when no aliases are configured', function () {
    expect(makePathAliasHolder()->getPathAliases())->toBe([]);
});
