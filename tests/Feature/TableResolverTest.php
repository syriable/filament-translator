<?php

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Harness-level coverage of {@see ConventionRegistry::resolveTableLabel()}: real Filament table
 * components are attached to a translating Livewire owner and resolved through the registered
 * `configureUsing` closures.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

it('resolves a table column label from the convention namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.columns.email.label' => 'Email',
    ], 'en');

    $table = Table::make(new TranslatingPage())->columns([TextColumn::make('email')]);

    expect($table->getColumn('email')->getLabel())->toBe('Email');
});

it('falls back to null for a null-allowed column attribute that is missing', function () {
    $table = Table::make(new TranslatingPage())->columns([TextColumn::make('email')]);

    expect($table->getColumn('email')->getTooltip())->toBeNull();
});

it('resolves a null-allowed column attribute when present', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.columns.email.tooltip' => 'The user email',
    ], 'en');

    $table = Table::make(new TranslatingPage())->columns([TextColumn::make('email')]);

    expect($table->getColumn('email')->getTooltip())->toBe('The user email');
});

it('resolves an absolute-key column label', function () {
    app('translator')->addLines([
        'globals.email.columns.label' => 'Absolute Email',
    ], 'en');

    $table = Table::make(new TranslatingPage())->columns([
        TextColumn::make('email')->conventionKey('globals.email', isAbsolute: true),
    ]);

    expect($table->getColumn('email')->getLabel())->toBe('Absolute Email');
});

it('falls back to null for a missing table heading', function () {
    $table = Table::make(new TranslatingPage())->columns([TextColumn::make('id')]);

    expect($table->getHeading())->toBeNull();
});

it('resolves a table heading when present', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.heading' => 'Users',
    ], 'en');

    $table = Table::make(new TranslatingPage())->columns([TextColumn::make('id')]);

    expect($table->getHeading())->toBe('Users');
});

it('resolves a table filter label', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.filters.active.label' => 'Active only',
    ], 'en');

    $table = Table::make(new TranslatingPage())->filters([Filter::make('active')]);

    expect($table->getFilter('active')->getLabel())->toBe('Active only');
});

it('resolves a table grouping label', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.groups.status.label' => 'By status',
    ], 'en');

    $table = Table::make(new TranslatingPage())->groups([Group::make('status')]);

    expect($table->getGroup('status')->getLabel())->toBe('By status');
});
