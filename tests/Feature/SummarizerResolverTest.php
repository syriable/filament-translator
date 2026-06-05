<?php

use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issue #62 — Table summarizer end-to-end key resolution. Summarizers resolve under their owning
 * column via the `columns.{column}.summarizers.{name}.{attribute}` convention key.
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

function countSummarizer(): ?object
{
    $table = Table::make(new TranslatingPage())->columns([
        TextColumn::make('amount')->summarize(Count::make()),
    ]);

    $summarizers = $table->getColumn('amount')->getSummarizers();

    return reset($summarizers) ?: null;
}

it('resolves a summarizer label from the column summarizers namespace', function () {
    app('translator')->addLines([
        'filament/pages/translating-page.table.columns.amount.summarizers.count.label' => 'Total rows',
    ], 'en');

    expect(countSummarizer()->getLabel())->toBe('Total rows');
});

it('surfaces the summarizer label key when the translation is missing', function () {
    expect(countSummarizer()->getLabel())
        ->toBe('filament/pages/translating-page.table.columns.amount.summarizers.count.label');
});
