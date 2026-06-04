<?php

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

beforeEach(function () {
    $this->langPath = sys_get_temp_dir() . '/ft-lang-' . Str::random(12);
    File::ensureDirectoryExists($this->langPath);
    app()->useLangPath($this->langPath);
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

afterEach(function () {
    File::deleteDirectory($this->langPath);
});

function translatingPageLangFile(): string
{
    return test()->langPath . '/en/filament/pages/translating-page.php';
}

it('writes nothing and keeps default behaviour when disabled', function () {
    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getLabel())
        ->toBe('filament/pages/translating-page.form.components.email.label')
        ->and(File::exists(translatingPageLangFile()))->toBeFalse();
});

it('scaffolds a missing required field label and displays the seeded value', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(TranslatorPlugin::make()->createMissingTranslationKeys());

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getLabel())->toBe('Email')
        ->and(require translatingPageLangFile())
        ->toBe(['form' => ['components' => ['email' => ['label' => 'Email']]]]);
});

it('does not scaffold optional (null-allowed) attributes', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(TranslatorPlugin::make()->createMissingTranslationKeys());

    $field = schemaField(Schema::make(new TranslatingPage())->components([TextInput::make('email')]), 'email');
    $field->getLabel();          // required -> scaffolded
    $placeholder = $field->getPlaceholder(); // optional -> null, not scaffolded

    expect($placeholder)->toBeNull()
        ->and(require translatingPageLangFile())
        ->toBe(['form' => ['components' => ['email' => ['label' => 'Email']]]]);
});

it('skips scaffolding in production', function () {
    app()->detectEnvironment(fn () => 'production');
    Filament::getCurrentOrDefaultPanel()->plugin(TranslatorPlugin::make()->createMissingTranslationKeys());

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);
    schemaField($schema, 'email')->getLabel();

    expect(File::exists(translatingPageLangFile()))->toBeFalse();
});

it('uses a custom seed-value resolver', function () {
    Filament::getCurrentOrDefaultPanel()->plugin(
        TranslatorPlugin::make()->createMissingTranslationKeys(using: fn (string $key) => 'X:' . $key),
    );

    $schema = Schema::make(new TranslatingPage())->components([TextInput::make('email')]);

    expect(schemaField($schema, 'email')->getLabel())
        ->toBe('X:filament/pages/translating-page.form.components.email.label');
});
