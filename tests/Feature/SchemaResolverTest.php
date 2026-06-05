<?php

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
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

// Issue #31 — no duplicate `tabs` segment (uses singular `tab`).

it('resolves a tab label without a duplicate tabs segment', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.tabs.tab.login.label' => 'Sign in',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        Tabs::make('x')->tabs([Tab::make('login')->schema([TextInput::make('email')])]),
    ]);

    $tab = collect($schema->getFlatComponents())->first(fn ($component) => $component instanceof Tab);

    expect($tab->getLabel())->toBe('Sign in');
});

it('resolves a field nested in a tab without a duplicate tabs segment', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.tabs.tab.login.schema.email.label' => 'Email',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        Tabs::make('x')->tabs([Tab::make('login')->schema([TextInput::make('email')])]),
    ]);

    expect(schemaField($schema, 'email')->getLabel())->toBe('Email');
});

// Issue #30 — hintIconTooltip resolution.

it('resolves hintIconTooltip from lang with single-argument hintIcon', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.password.hint_icon_tooltip' => 'Min 8 chars',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        TextInput::make('password')->hintIcon(Heroicon::QuestionMarkCircle),
    ]);

    expect(schemaField($schema, 'password')->getHintIconTooltip())->toBe('Min 8 chars');
});

it('lets a two-argument hintIcon override the wired tooltip', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.password.hint_icon_tooltip' => 'From lang',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([
        TextInput::make('password')->hintIcon(Heroicon::QuestionMarkCircle, 'Explicit'),
    ]);

    expect(schemaField($schema, 'password')->getHintIconTooltip())->toBe('Explicit');
});

// Issue #29 — Schemas\Components\Text support.

it('resolves Text content from lang when addressed via key()', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.form.components.or.content' => 'Or',
    ], 'en');

    $schema = Schema::make(new TranslatingPage())->components([Text::make(null)->key('or')]);

    $text = collect($schema->getFlatComponents())->first(fn ($component) => $component instanceof Text);

    expect($text->getContent())->toBe('Or');
});

it('keeps explicit Text content without a lang lookup', function () {
    $schema = Schema::make(new TranslatingPage())->components([Text::make('Literal')->key('lit')]);

    $text = collect($schema->getFlatComponents())->first(fn ($component) => $component instanceof Text);

    expect($text->getContent())->toBe('Literal');
});

// Issue #25 — deprecated Placeholder no longer registered.

it('does not register the deprecated Placeholder schema type', function () {
    $types = invade(new ConventionRegistry())->monitoredSchemaTypes;

    expect($types)->not->toContain(Placeholder::class)
        ->and($types)->toContain(Entry::class);
});
