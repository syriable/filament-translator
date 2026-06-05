<?php

use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issue #68 — Filament v4/v5 matrix: Schemas\Components\Text only exists on Filament v5, so the
 * registry guards its wiring with class_exists(). registerDefaults() must boot cleanly on both
 * versions, and on v5 the Text content must resolve from lang.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

it('boots cleanly regardless of Filament version', function () {
    expect(fn () => app(ConventionRegistry::class)->registerDefaults())->not->toThrow(Throwable::class);
});

it('wires Schemas\\Components\\Text content when the class exists (Filament v5)', function () {
    if (! class_exists(Text::class)) {
        expect(class_exists(Text::class))->toBeFalse();

        return;
    }

    app('translator')->addLines([
        'filament/pages/translating-page.form.components.or.content' => 'Or',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([Text::make(null)->key('or')]);
    $text = collect($schema->getFlatComponents())->first(fn ($component) => $component instanceof Text);

    expect($text->getContent())->toBe('Or');
})->skip(fn () => ! class_exists(Text::class), 'Schemas\\Components\\Text is only available on Filament v5.');
