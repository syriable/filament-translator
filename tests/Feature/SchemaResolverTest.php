<?php

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Harness-level coverage of {@see ConventionRegistry::resolveSchemaLabel()}: real Filament schema
 * components are attached to a translating Livewire owner and their labels are resolved through
 * the registered `configureUsing` closures (no full page render).
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

it('resolves a form field label from the convention namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.email.label' => 'Email address',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getLabel())->toBe('Email address');
});

it('falls back to null for a null-allowed field attribute that is missing', function () {
    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getPlaceholder())->toBeNull();
});

it('resolves a null-allowed field attribute when present', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.email.placeholder' => 'you@example.com',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getPlaceholder())->toBe('you@example.com');
});

it('resolves an absolute-key field label without the namespace prefix', function () {
    app('translator')->addLines([
        'globals.email.components.label' => 'Absolute Email',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        TextInput::make('email')->conventionKey('globals.email', isAbsolute: true),
    ]);

    expect(schemaField($schema, 'email')->getLabel())->toBe('Absolute Email');
});

it('walks parent components to resolve a nested field label', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.Details.schema.name.label' => 'Full name',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        Section::make('Details')->components([TextInput::make('name')]),
    ]);

    expect(schemaField($schema, 'name')->getLabel())->toBe('Full name');
});
