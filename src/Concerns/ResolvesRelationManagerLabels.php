<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;

/**
 * @mixin RelationManager
 */
trait ResolvesRelationManagerLabels
{
    public static function resolveLabel(string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $resourceClass = static::resolveResourceClass();

        if (! $resourceClass) {
            return null;
        }

        $relationManagerKey = str(static::class)
            ->classBasename()
            ->snake();

        $conventionKey = str("relation_managers.{$relationManagerKey}")
            ->when(filled($pageLabelContextKey))->append(".{$pageLabelContextKey}")
            ->when(blank($pageLabelContextKey) && $pageLabelContext === PageLabelContext::Table)->append('.table')
            ->when(blank($pageLabelContextKey) && $pageLabelContext === PageLabelContext::Form)->append('.form')
            ->when(blank($pageLabelContextKey) && $pageLabelContext === PageLabelContext::Actions)->append('.actions')
            ->append(".{$key}");

        return $resourceClass::resolveLabel($conventionKey, $replace, $number, $allowNull);
    }

    protected static function resolveResourceClass(): ?string
    {
        $resourceClass = str(static::class)
            ->before('RelationManagers\\')
            ->trim('\\')
            ->toString();

        if (class_exists($resourceClass)) {
            return $resourceClass;
        }

        $resourceNamespace = str(static::class)
            ->before('RelationManagers\\')
            ->trim('\\')
            ->toString();

        $folderName = str($resourceNamespace)
            ->afterLast('\\')
            ->toString();

        $resourceClassName = Str::singular($folderName).'Resource';
        $resourceClass = "{$resourceNamespace}\\{$resourceClassName}";

        if (class_exists($resourceClass)) {
            return $resourceClass;
        }

        return null;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return static::resolveLabel('title', allowNull: true) ?? parent::getTitle($ownerRecord, $pageClass);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return static::resolveLabel('badge', allowNull: true) ?? parent::getBadge($ownerRecord, $pageClass);
    }

    public static function getBadgeTooltip(Model $ownerRecord, string $pageClass): ?string
    {
        return static::resolveLabel('badge_tooltip', allowNull: true) ?? parent::getBadgeTooltip($ownerRecord, $pageClass);
    }

    public static function getModelLabel(): ?string
    {
        return static::resolveLabel('model_label', allowNull: true) ?? parent::getModelLabel();
    }

    public static function getPluralModelLabel(): ?string
    {
        return static::resolveLabel('plural_model_label', allowNull: true) ?? parent::getModelLabel();
    }
}
