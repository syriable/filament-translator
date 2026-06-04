<?php

use Syriable\Filament\Plugins\Translator\Enums\ActionScope;
use Syriable\Filament\Plugins\Translator\Enums\InfolistScope;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use Syriable\Filament\Plugins\Translator\Enums\ResolutionMode;
use Syriable\Filament\Plugins\Translator\Enums\SchemaScope;
use Syriable\Filament\Plugins\Translator\Enums\TableScope;

it('exposes the expected ActionScope cases', function () {
    expect(ActionScope::Schema->value)->toBe('schema')
        ->and(ActionScope::Notifications->value)->toBe('notifications')
        ->and(ActionScope::cases())->toHaveCount(2);
});

it('exposes the expected InfolistScope cases', function () {
    expect(InfolistScope::Entries->value)->toBe('entries')
        ->and(InfolistScope::cases())->toHaveCount(1);
});

it('exposes the expected PageLabelContext cases', function () {
    expect(PageLabelContext::Actions->value)->toBe('actions')
        ->and(PageLabelContext::Schemas->value)->toBe('schemas')
        ->and(PageLabelContext::Form->value)->toBe('form')
        ->and(PageLabelContext::Infolist->value)->toBe('infolist')
        ->and(PageLabelContext::Table->value)->toBe('table')
        ->and(PageLabelContext::cases())->toHaveCount(5);
});

it('exposes the expected ResolutionMode cases', function () {
    expect(ResolutionMode::Strict->value)->toBe('strict')
        ->and(ResolutionMode::Balanced->value)->toBe('balanced')
        ->and(ResolutionMode::Loose->value)->toBe('loose')
        ->and(ResolutionMode::cases())->toHaveCount(3);
});

it('exposes the expected SchemaScope cases', function () {
    expect(SchemaScope::Components->value)->toBe('components')
        ->and(SchemaScope::cases())->toHaveCount(1);
});

it('exposes the expected TableScope cases', function () {
    expect(TableScope::Actions->value)->toBe('actions')
        ->and(TableScope::BulkActions->value)->toBe('bulk_actions')
        ->and(TableScope::Columns->value)->toBe('columns')
        ->and(TableScope::Filters->value)->toBe('filters')
        ->and(TableScope::Groups->value)->toBe('groups')
        ->and(TableScope::cases())->toHaveCount(5);
});

it('resolves enum cases from their backing value', function () {
    expect(TableScope::from('bulk_actions'))->toBe(TableScope::BulkActions)
        ->and(PageLabelContext::tryFrom('nope'))->toBeNull();
});
