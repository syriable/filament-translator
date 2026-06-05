<?php

namespace Syriable\Filament\Plugins\Translator\Enums;

use Syriable\Filament\Plugins\Translator\ConventionRegistry;

/**
 * Reserved scope marker for infolist label resolution.
 *
 * Infolist entries currently resolve through the shared schema pipeline under the `form` /
 * `infolist` context (see {@see ConventionRegistry}), so this
 * enum is intentionally not wired into a dedicated resolution path yet. It is kept as a stable,
 * documented placeholder for a future infolist-specific scope; see the "Infolist scope (reserved
 * API)" section of the README. Do not rely on additional cases until that work lands.
 */
enum InfolistScope: string
{
    case Entries = 'entries';
}
