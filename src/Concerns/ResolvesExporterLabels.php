<?php

namespace Syriable\Filament\Plugins\Translator\Concerns;

use Countable;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Stringable;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;

trait ResolvesExporterLabels
{
    use ResolvesConventionNamespace;

    public static function resolveLabel(string $key, array $replace = [], Countable | float | int | null $number = null, bool $allowNull = false, ?PageLabelContext $pageLabelContext = null, ?string $pageLabelContextKey = null): mixed
    {
        $conventionKey = str(static::resolveConventionNamespace())
            ->when(filled($pageLabelContextKey))->append(".{$pageLabelContextKey}")
            ->when(blank($pageLabelContextKey) && $pageLabelContext, fn (Stringable $str) => $str->append(".{$pageLabelContext->value}"))
            ->append(".{$key}")
            ->toString();

        return static::lookupConventionKey($conventionKey, $replace, $number, $allowNull);
    }

    public static function getCompletedNotificationTitle(Export $export): string
    {
        return static::resolveLabel(key: 'notifications.completed.title', allowNull: true) ?? parent::getCompletedNotificationTitle($export);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = static::resolveLabel(
            key: 'notifications.completed.body.default',
            replace: [
                'successfulRows' => number_format($export->successful_rows),
            ],
            number: $export->successful_rows,
            allowNull: true,
        );

        // Fall back to Filament's native body when no convention translation exists, so this
        // method always honours its `string` return type (instead of returning null in production).
        if (blank($body)) {
            return parent::getCompletedNotificationBody($export);
        }

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $failedBody = static::resolveLabel(
                key: 'notifications.completed.body.failed',
                replace: [
                    'failedRows' => number_format($failedRowsCount),
                ],
                number: $failedRowsCount,
                allowNull: true,
            );

            if (filled($failedBody)) {
                $body .= ' ' . $failedBody;
            }
        }

        return $body;
    }
}
