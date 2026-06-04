<?php

use Filament\Actions\Action;
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

it('defaults to a null convention key and a non-absolute flag', function () {
    $component = TextInput::make('name');

    expect($component->getConventionKey())->toBeNull()
        ->and($component->isConventionKeyAbsolute())->toBeFalse();
});

it('toggles the absolute flag via conventionKeyAbsolute()', function () {
    $component = TextInput::make('name')->conventionKeyAbsolute(true);

    expect($component->isConventionKeyAbsolute())->toBeTrue();
});

it('evaluates a closure convention key', function () {
    $component = TextInput::make('name')->conventionKey(fn () => 'dynamic.key');

    expect($component->getConventionKey())->toBe('dynamic.key');
});

it('registers the macros on view components such as actions', function () {
    $action = Action::make('save')->conventionKey('globals.save', isAbsolute: true);

    expect($action->getConventionKey())->toBe('globals.save')
        ->and($action->isConventionKeyAbsolute())->toBeTrue();
});
