<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issue #58 — Wizard and Wizard\Step label convention paths.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

function wizardSchema(): Schema
{
    return Schema::make(new TranslatingPage())->components([
        Wizard::make([
            Step::make('details')->schema([TextInput::make('email')]),
        ]),
    ]);
}

it('resolves a wizard step label from the convention namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.wizard.steps.details.label' => 'Your details',
    ], 'en');

    $step = collect(wizardSchema()->getFlatComponents())->first(fn ($component) => $component instanceof Step);

    expect($step->getLabel())->toBe('Your details');
});

it('surfaces the wizard step label key when the translation is missing', function () {
    $step = collect(wizardSchema()->getFlatComponents())->first(fn ($component) => $component instanceof Step);

    expect($step->getLabel())->toBe('filament/pages/translating-page.form.components.wizard.steps.details.label');
});

it('walks the wizard parent path to resolve a nested field label', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.wizard.steps.details.schema.email.label' => 'Email address',
    ], 'en');

    expect(schemaField(wizardSchema(), 'email')->getLabel())->toBe('Email address');
});
