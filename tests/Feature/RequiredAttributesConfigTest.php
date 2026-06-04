<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

it('registers a publishable config file', function () {
    expect(config('filament-translator'))->toBeArray()
        ->and(config('filament-translator.required'))->toBe([]);
});

it('keeps the default required/optional split with no overrides', function () {
    $registry = invade(new ConventionRegistry());

    expect($registry->schemaLabelAttributes['label'])->toBeFalse()        // required
        ->and($registry->schemaLabelAttributes['placeholder'])->toBeTrue(); // optional
});

it('applies required overrides to the attribute maps across contexts', function () {
    config()->set('filament-translator.required', ['placeholder' => true, 'label' => false]);

    $registry = invade(new ConventionRegistry());

    expect($registry->schemaLabelAttributes['placeholder'])->toBeFalse()    // now required
        ->and($registry->schemaLabelAttributes['label'])->toBeTrue()        // now optional
        ->and($registry->columnLabelAttributes['placeholder'])->toBeFalse() // applied everywhere
        ->and($registry->filterLabelAttributes['placeholder'])->toBeFalse();
});

it('leaves unrelated attributes untouched', function () {
    config()->set('filament-translator.required', ['placeholder' => true]);

    $registry = invade(new ConventionRegistry());

    expect($registry->schemaLabelAttributes['label'])->toBeFalse()   // unchanged (still required)
        ->and($registry->schemaLabelAttributes['hint'])->toBeTrue(); // unchanged (still optional)
});

it('makes an optional attribute required end-to-end via config', function () {
    config()->set('filament-translator.required', ['placeholder' => true]);
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    // A required-but-missing attribute now surfaces its convention key instead of null.
    expect(schemaField($schema, 'email')->getPlaceholder())
        ->toBe('filament/pages/translating-page.form.components.email.placeholder');
});

it('can make a required attribute optional via config', function () {
    config()->set('filament-translator.required', ['label' => false]);
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    // Label is now optional, so it falls back to Filament's generated label instead of the key.
    expect(schemaField($schema, 'email')->getLabel())->toBe('Email');
});
