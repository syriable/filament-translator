<?php

namespace Syriable\Filament\Plugins\Translator\Support;

use Filament\Actions\Contracts\HasActions;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;

/**
 * Thin adapter that isolates every read of a Filament/Livewire internal behind a named method.
 *
 * Convention-based label resolution needs values Filament does not expose publicly — closure-backed
 * property state (`label`, `heading`, `content`, `key`), prebuilt default labels, and the
 * Livewire action caches used to discover the mounting action without triggering Filament's
 * `getMountedAction()` (which would recurse during action caching).
 *
 * Centralising the `invade()` calls here keeps the coupling to Filament internals in one place, so a
 * Filament major upgrade that renames an internal only needs changes in this adapter rather than
 * scattered across {@see ConventionRegistry}. Reads are
 * deliberately *not* cached: most reflect live, mutable component state (e.g. whether `label` is
 * still a Closure) and caching would return stale values across reconfiguration.
 */
final class FilamentInternals
{
    /**
     * Read a non-public property from a Filament component (for example the raw, possibly
     * closure-valued `label`/`heading`/`content`/`key` before evaluation).
     */
    public static function property(object $component, string $property): mixed
    {
        return invade($component)->{$property};
    }

    /**
     * Cached actions registered on a Livewire component (keyed by action name).
     *
     * @return array<string, mixed>
     */
    public static function cachedActions(object $livewire): array
    {
        return invade($livewire)->cachedActions ?? [];
    }

    /**
     * Cached *mounted* actions on a Livewire component. Read directly instead of via
     * `getMountedAction()` to avoid recursion while an action is being cached.
     *
     * @return array<array-key, mixed>
     */
    public static function cachedMountedActions(object $livewire): array
    {
        if (! $livewire instanceof HasActions) {
            return [];
        }

        return invade($livewire)->cachedMountedActions ?? [];
    }

    /**
     * Run a prebuilt component's protected `setUp()` so its framework default labels are populated.
     */
    public static function callSetUp(object $component): void
    {
        invade($component)->setUp();
    }
}
