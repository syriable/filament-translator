<?php

use Filament\Actions\Action;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Harness-level coverage of {@see ConventionRegistry::resolveActionLabel()}: an action bound to a
 * translating Livewire owner resolves its label through the registered `configureUsing` closure.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

it('resolves a page action label from the convention namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.actions.delete.label' => 'Remove',
    ], 'en');

    $action = Action::make('delete')->livewire(new TranslatingPage());

    expect($action->getLabel())->toBe('Remove');
});

it('resolves an absolute-key action label', function () {
    app('translator')->addLines([
        'globals.delete.label' => 'Delete forever',
    ], 'en');

    $action = Action::make('delete')
        ->livewire(new TranslatingPage())
        ->conventionKey('globals.delete', isAbsolute: true);

    expect($action->getLabel())->toBe('Delete forever');
});

it('leaves the action label unresolved when the owner does not translate conventionally', function () {
    expect(ConventionRegistry::resolveActionLabel(Action::make('delete'), null, 'label'))->toBeNull();
});
