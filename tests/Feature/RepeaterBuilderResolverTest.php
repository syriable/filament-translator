<?php

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issue #64 — Repeater and Builder block nested schema key paths.
 *
 * Repeater/Builder children only exist once the schema is filled with state, so each scenario
 * seeds a default item and fills the schema before walking into the child component containers.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

function firstNestedField(Repeater | Builder $parent): TextInput
{
    return collect($parent->getChildComponentContainers())
        ->flatMap(fn ($container) => $container->getComponents())
        ->first(fn ($component) => $component instanceof TextInput);
}

it('resolves a field label nested inside a Repeater', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.data.components.items.schema.name.label' => 'Item name',
    ], 'en');

    $repeater = Repeater::make('items')->schema([TextInput::make('name')])->default([['name' => null]]);
    Schema::make(new TranslatingPage())->statePath('data')->components([$repeater])->fill();

    expect(firstNestedField($repeater)->getLabel())->toBe('Item name');
});

it('resolves a field label nested inside a Builder block', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.data.components.blocks.blocks.hero.schema.title.label' => 'Hero title',
    ], 'en');

    $builder = Builder::make('blocks')
        ->blocks([Builder\Block::make('hero')->schema([TextInput::make('title')])])
        ->default([['type' => 'hero', 'data' => ['title' => null]]]);
    Schema::make(new TranslatingPage())->statePath('data')->components([$builder])->fill();

    expect(firstNestedField($builder)->getLabel())->toBe('Hero title');
});
