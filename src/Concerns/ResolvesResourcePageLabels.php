<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use UnitEnum;

/**
 * @mixin Page
 */
trait ResolvesResourcePageLabels
{
    public function getTitle(): string
    {
        return static::resolveLabel('title', allowNull: true) ?? parent::getTitle();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return static::resolveLabel('subheading', allowNull: true) ?? parent::getSubheading();
    }

    // TODO: there is a conflicting return type between the `Resourcs\Pages\Page` class and all the other `Resources\Pages\*Record` classes.
    //    public function getBreadcrumb(): string
    //    {
    //        return static::resolveLabel('breadcrumb', allowNull: true) ?? parent::getBreadcrumb();
    //    }

    public static function getNavigationLabel(): string
    {
        return static::resolveLabel('navigation_label', allowNull: true) ?? parent::getNavigationLabel();
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return static::resolveLabel('navigation_group', allowNull: true) ?? parent::getNavigationGroup();
    }

    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $resourcePageName = static::getResourcePageName();

        if (
            (
                (is_a(static::class, CreateRecord::class, true) && $resourcePageName === 'create')
                || (is_a(static::class, EditRecord::class, true) && $resourcePageName === 'edit')
                || (is_a(static::class, ViewRecord::class, true) && $resourcePageName === 'view')
            )
            && $pageLabelContext === PageLabelContext::Form
        ) {
            return static::getResource()::resolveLabel($key, $replace, $number, $allowNull, $pageLabelContext);
        }

        if (
            is_a(static::class, ViewRecord::class, true)
            && $resourcePageName === 'view'
            && $pageLabelContext === PageLabelContext::Form
        ) {
            return static::getResource()::resolveLabel($key, $replace, $number, $allowNull, $pageLabelContext);
        }

        if (
            is_a(static::class, ListRecords::class, true)
            && $resourcePageName === 'index'
            && $pageLabelContext === PageLabelContext::Table
        ) {
            return static::getResource()::resolveLabel($key, $replace, $number, $allowNull, $pageLabelContext);
        }

        $pageKey = str(static::class)
            ->classBasename()
            ->snake();

        if ($pageLabelContextKey) {
            return static::getResource()::resolveLabel("pages.{$pageKey}.{$pageLabelContextKey}.{$key}", $replace, $number, $allowNull);
        }

        if ($pageLabelContext) {
            return static::getResource()::resolveLabel("pages.{$pageKey}.{$pageLabelContext->value}.{$key}", $replace, $number, $allowNull);
        }

        return static::getResource()::resolveLabel("pages.{$pageKey}.{$key}", $replace, $number, $allowNull);
    }
}
