<?php

use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;
use TranslatorFixtures\App\Filament\Schemas\Components\Separator;

/**
 * Issue #28 — register custom schema components and their translatable attributes via
 * config('filament-translator.components').
 */
beforeEach(function () {
    config()->set('filament-translator.components', [Separator::class => ['text' => false]]);
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

function separator(Schema $schema): Separator
{
    return collect($schema->getFlatComponents())->first(fn ($component) => $component instanceof Separator);
}

it('resolves a registered custom component attribute from lang', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.or.text' => 'Or',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([Separator::make('or')]);

    expect(separator($schema)->getText())->toBe('Or');
});

it('surfaces the convention key when the custom attribute translation is missing', function () {
    $schema = Schema::make(new TranslatingPage())->components([Separator::make('or')]);

    expect(separator($schema)->getText())
        ->toBe('filament/pages/translating-page.form.components.or.text');
});

it('honors the required override for registered custom attributes', function () {
    // Make the custom `text` attribute optional via the global required map.
    config()->set('filament-translator.required', ['text' => false]);
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);

    $schema = Schema::make(new TranslatingPage())->components([Separator::make('maybe')]);

    // Optional + missing => null instead of the surfaced key.
    expect(separator($schema)->getText())->toBeNull();
});
