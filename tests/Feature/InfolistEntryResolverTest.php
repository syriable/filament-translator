<?php

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issue #65 — Infolist entry label resolution (monitored Entry type).
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

function infolistEntry(string $name): TextEntry
{
    $schema = Schema::make(new TranslatingPage())->components([TextEntry::make($name)]);

    return collect($schema->getFlatComponents())
        ->first(fn ($component) => $component instanceof TextEntry && $component->getName() === $name);
}

it('resolves an infolist entry label from the convention namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.email.label' => 'Email address',
    ], 'en');

    expect(infolistEntry('email')->getLabel())->toBe('Email address');
});

it('resolves a null-allowed infolist entry attribute when present', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.email.hint' => 'The user email',
    ], 'en');

    expect(infolistEntry('email')->getHint())->toBe('The user email');
});

it('falls back to null for a missing null-allowed infolist entry attribute', function () {
    expect(infolistEntry('email')->getHint())->toBeNull();
});
