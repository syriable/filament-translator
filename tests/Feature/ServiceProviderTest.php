<?php

use Filament\Forms\Components\TextInput;

it('registers the conventionKey macros on Filament components', function () {
    $component = TextInput::make('name')->conventionKey('custom.segment.name');

    expect($component->getConventionKey())->toBe('custom.segment.name')
        ->and($component->isConventionKeyAbsolute())->toBeFalse();
});

it('marks a convention key as absolute', function () {
    $component = TextInput::make('active')->conventionKey('globals.active', isAbsolute: true);

    expect($component->getConventionKey())->toBe('globals.active')
        ->and($component->isConventionKeyAbsolute())->toBeTrue();
});
