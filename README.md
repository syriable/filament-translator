![Syriable Filament Translator](art/header-img.svg)

# Syriable Filament Translator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syriable/filament-translator.svg?style=flat-square)](https://packagist.org/packages/syriable/filament-translator)
[![GitHub Tests Action Status](https://github.com/syriable/filament-translator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/syriable/filament-translator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/syriable/filament-translator/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/syriable/filament-translator/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Convention-based automatic translations for [Filament](https://filamentphp.com) panels — forms, tables, actions, infolists, resources, pages, widgets, importers, and exporters.

**Syriable Filament Translator** derives translation keys from your PHP class names and Filament component names so UI code stays free of hard-coded copy. The package registers lazy label resolvers at boot; when a lang entry is missing, Filament’s default label is preserved.

## Features

- **Convention-based labels** — keys are derived from the owner class and component name; no hard-coded copy. ([what gets translated](#what-gets-translated))
- **Broad coverage** — forms, infolists, tables, columns, filters, summarizers, grouping, actions, importers/exporters, plus page/resource/cluster/widget metadata.
- **Path aliases** — map namespaces outside Filament’s defaults, e.g. `App\Livewire` → `livewire`. ([docs](#path-aliases))
- **Component macros** — override or pin a component’s key, including absolute keys. ([docs](#component-macros))
- **Automatic key creation** — scaffold missing required keys into your lang files as you browse, during local development. ([docs](#automatic-key-creation-local-development))
- **Configurable required attributes** — choose which attributes must be translated via config. ([docs](#configuration))
- **Custom schema components** — register your own components for convention resolution; Filament’s `Text` is supported out of the box. ([docs](#custom-schema-components))
- **Hint-icon tooltips** — translate `hintIconTooltip` via the `hint_icon_tooltip` key. ([docs](#hint-icon-tooltips))
- **Translatable base classes & traits** — drop-in bases/traits for pages, resources, clusters, widgets, importers, and more. ([docs](#base-classes))
- **Graceful fallback** — any key you omit falls back to Filament’s native label.

## Requirements

- PHP 8.3+
- Laravel 11, 12, or 13
- Filament 4 or 5

## Installation

You can install the package via Composer:

```bash
composer require syriable/filament-translator
```

Register the plugin on your Filament panel:

```php
use Filament\Panel;
use Syriable\Filament\Plugins\Translator\TranslatorPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            TranslatorPlugin::make()
                ->pathAliases([
                    'App\\Livewire' => 'livewire',
                ]),
        ]);
}
```

The `TranslatorServiceProvider` is auto-discovered and registers `conventionKey()` macros on Filament schema components.

## Usage

### Panel plugin

`TranslatorPlugin` boots the convention registry when the panel starts. Register it on every panel that should resolve labels automatically.

### Path aliases

Map namespace fragments to lang file paths when your classes live outside Filament’s default directory structure:

```php
TranslatorPlugin::make()
    ->pathAliases([
        'App\\Livewire' => 'livewire',
    ]);

// Replace all aliases instead of merging:
TranslatorPlugin::make()
    ->pathAliases(['App\\Livewire' => 'livewire'], merge: false);
```

### Automatic key creation (local development)

Hand-writing every lang key while building an interface is tedious. Enable
`createMissingTranslationKeys()` and the package scaffolds any **missing required label** into the
correct lang file as you load pages — creating the file, the nested array path, and a humanised
default value — so you’re left with a ready-to-translate stub instead of a raw key on screen.

```php
TranslatorPlugin::make()
    ->createMissingTranslationKeys();
```

Requesting `livewire/auth/login.form.components.actions.forgot-password.label`, for example, writes
`lang/{locale}/livewire/auth/login.php`:

```php
return [
    'form' => [
        'components' => [
            'actions' => [
                'forgot-password' => [
                    'label' => 'Forgot Password',
                ],
            ],
        ],
    ],
];
```

- **Local only.** Writes are always skipped in production regardless of the flag — lang files are
  never written on live requests.
- **Required labels only.** Optional attributes (placeholder, helper text, tooltip, …) are skipped
  to keep lang files lean, and existing values are never overwritten.

Customise the seeded value, or gate activation behind a condition, with the method arguments:

```php
// Seed every new key with an empty string instead of a humanised guess:
TranslatorPlugin::make()->createMissingTranslationKeys(using: fn (string $key) => '');

// Enable only in the local environment:
TranslatorPlugin::make()->createMissingTranslationKeys(fn () => app()->isLocal());
```

### Plugin helpers

```php
TranslatorPlugin::get();      // plugin instance on the current panel
TranslatorPlugin::isActive(); // whether the plugin is registered
```

### Standalone Livewire pages

Filament schemas on guest routes still need the registry booted once:

```php
use Syriable\Filament\Plugins\Translator\ConventionRegistry;

app(ConventionRegistry::class)->registerDefaults();
```

Register `TranslatorPlugin` on a panel with `pathAliases()` when you need namespace remapping; aliases are read from the active plugin during label resolution. When the plugin is not registered on the active panel, resolution falls back to empty path aliases instead of throwing, so guest routes keep working.

#### Boot behaviour & double `registerDefaults()`

`TranslatorPlugin::boot()` calls `registerDefaults()` automatically for every panel the plugin is
registered on, so **panel pages need no manual boot**. Standalone Livewire components rendered
*outside* a panel (guest/auth routes such as `/login`) are not covered by panel boot, so the host
app must call `registerDefaults()` itself — typically once from a service provider:

```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    app(ConventionRegistry::class)->registerDefaults();
}
```

It is safe to use **both** patterns at once (panel plugin + manual boot). `registerDefaults()` is
**idempotent**: it tracks the active Filament component manager and a signature of the current
configuration, so repeated calls within the same request — and repeated worker boots under
[Laravel Octane](https://laravel.com/docs/octane) — skip re-wiring instead of stacking duplicate
`configureUsing` hooks. Re-registration only happens when the component manager instance changes
(a fresh app) or when the relevant configuration (`required`, custom `components`, path aliases)
changes.

> **Boot cost trade-off.** Wiring is registered **eagerly** for all supported component families in a
> single pass rather than lazily per component type. The pass is cheap (it only registers
> `configureUsing` closures; no resolution happens until a component is rendered) and the idempotency
> guard above ensures it runs at most once per app instance. Eager-but-idempotent wiring keeps the
> boot path simple and predictable; lazy per-type registration is intentionally **not** used because
> its complexity is not justified once duplicate boots are already elided.

#### Path aliases without an active panel

Path aliases live on the `TranslatorPlugin` instance and are read from the **active panel** during
resolution. On a standalone Livewire route there may be no panel plugin active, in which case
`TranslatorPlugin::get()` returns a plugin with **empty path aliases** and resolution falls back to
the default `Filament`-prefixed namespace derivation (it never throws). If a guest route needs
custom namespace remapping, register `TranslatorPlugin` (with your `pathAliases()`) on the default
panel so the aliases are available even when that panel is not the one rendering the page.

| Scenario                                   | Path aliases               | Boot requirement                          |
| ------------------------------------------ | -------------------------- | ----------------------------------------- |
| Panel page (plugin registered)             | From the active panel      | Automatic via `TranslatorPlugin::boot()`  |
| Standalone Livewire, plugin on any panel   | From that panel's plugin   | Manual `registerDefaults()` in a provider |
| Standalone Livewire, no plugin anywhere    | Empty (default derivation) | Manual `registerDefaults()` in a provider |

## Configuration

By default only the primary attributes (`label`, section `heading`, placeholder `content`, model labels, …) are **required** — a missing translation surfaces the convention key — while attributes such as `placeholder`, `tooltip`, `helperText`, and `hint` are optional and fall back to `null`.

Publish the config file to change which attributes are required:

```bash
php artisan vendor:publish --tag="filament-translator-config"
```

`config/filament-translator.php` exposes a `required` map keyed by attribute (method) name. `true` makes the attribute required, `false` makes it optional; anything not listed keeps the default. Overrides apply across every context where the attribute appears (forms, tables, columns, filters, actions, summarizers):

```php
return [
    'required' => [
        'placeholder' => true, // require placeholders everywhere
        'tooltip' => true,     // require tooltips
        'label' => false,      // make labels optional
    ],
];
```

Required attributes also participate in [automatic key creation](#automatic-key-creation-local-development) when that feature is enabled.

### Custom schema components

Register your own schema components (extending `Filament\Schemas\Components\Component`) so their
attributes resolve through the same convention pipeline as first-party fields. Map each component
class to an `attribute => allowNull` list (`false` = required, `true` = optional), in the same
shape as the built-in attribute maps:

```php
// config/filament-translator.php
'components' => [
    \App\Filament\Schemas\Components\Separator::class => [
        'text' => false,
    ],
],
```

```php
// Used unchanged in a form…
Separator::make('or'),

// …resolved from lang/{locale}/livewire/auth/login.php:
'form' => ['components' => ['or' => ['text' => 'Or']]],
```

The `required` overrides above apply to registered attributes too, and they participate in automatic
key creation when enabled.

Filament’s first-party `Filament\Schemas\Components\Text` (static schema copy) is supported out of
the box. Address it via `key()` so the content resolves from lang; explicit content stays literal:

```php
Text::make(null)->key('or'); // resolves {namespace}.form.components.or.content
Text::make('Or');            // literal — no lang lookup
```

### Hint-icon tooltips

Hint-icon tooltips are translated from the `hint_icon_tooltip` key. Set the icon in PHP with a
**single argument** and put the copy in lang:

```php
TextInput::make('password')
    ->hintIcon('heroicon-o-question-mark-circle'); // one argument — tooltip comes from lang
```

```php
'form' => ['components' => ['password' => ['hint_icon_tooltip' => 'Minimum 8 characters.']]],
```

Passing a second argument — `->hintIcon($icon, $tooltip)` — sets the tooltip explicitly and skips
the translation. This needs a Filament version that guards `hintIcon()` with `func_num_args()`;
older releases clear the tooltip even with a single argument.

#### Filament version compatibility

Single-argument `->hintIcon($icon)` tooltip preservation depends on Filament guarding `hintIcon()`
with `func_num_args()` so a single call does not null out a previously wired tooltip:

| Filament                          | Single-arg `hintIcon()` preserves the wired tooltip |
| --------------------------------- | --------------------------------------------------- |
| 4.x / 5.x (with the guard)        | Yes                                                  |
| Older releases without the guard  | No — the tooltip is cleared even with one argument  |

The package does not hard-pin this behaviour; instead the suite probes it at runtime via
`preservesSingleArgHintIconTooltip()` (`tests/Feature/SchemaResolverTest.php`) and skips the
relevant assertion when the running Filament version lacks the guard. Mirror that probe in your own
CI if you support a wide Filament range, and watch this section's matrix when upgrading.

### Infolist scope (reserved API)

The `Enums\InfolistScope` enum is a **reserved/future API placeholder**. Infolist entries are
fully translated today, but they resolve through the shared schema pipeline under the `form` /
`infolist` context rather than a dedicated infolist scope — so unlike `ActionScope`, `SchemaScope`,
and `TableScope`, `InfolistScope` is intentionally **not** wired into a resolution path yet. It is
kept as a stable marker for a possible infolist-specific scope; treat it as reserved and do not
depend on additional cases until that work ships.

## What gets translated

`ConventionRegistry` wires lazy resolvers through Filament’s `configureUsing` hooks. Missing translations fall back to Filament’s native labels.

| Area                       | Translated attributes                                                                                                                                                                                              |
| -------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **Actions**                | Label, tooltip, badge, modal heading/description, submit/cancel labels, success/failure notification titles                                                                                                        |
| **Forms & infolists**      | Field labels, placeholders, helper text, hints, hint-icon tooltips, prefixes/suffixes, validation attributes, section headings/descriptions, tabs, wizard steps, `Text` content, repeater action labels, select create/edit modal headings, loading messages |
| **Tables**                 | Search placeholder, model labels, heading/description, default sort label, empty state heading/description, actions column label                                                                                   |
| **Columns**                | Label, description, tooltip, prefix/suffix, placeholder, default value, validation attribute                                                                                                                       |
| **Filters**                | Label, indicator, placeholder, true/false labels, constraint labels                                                                                                                                                |
| **Summarizers & grouping** | Label, prefix, suffix, grouping labels                                                                                                                                                                             |
| **Importers & exporters**  | Column and action labels                                                                                                                                                                                           |

> **Monitored column types.** Automatic column-label resolution is wired for `TextColumn`,
> `IconColumn`, `ColorColumn`, `ToggleColumn`, and `SelectColumn`. Custom or other column types are
> not translated automatically — set their key explicitly with the [`conventionKey()` macro](#component-macros).

Static metadata on pages, resources, clusters, widgets, relation managers, and resource pages is resolved through the `Resolves*` traits (see below).

## Translation key convention

```
{owner-path}.{context}.{component-name}.{attribute}
```

Examples:

| UI source                        | Lang key                                                                 |
| -------------------------------- | ------------------------------------------------------------------------ |
| `UserResource` form field `name` | `filament/resources/user-resource.form.name.label`                       |
| Login page action `login`        | `livewire/auth/login.actions.login.label`                                |
| Field inside a tab               | `livewire/auth/login.form.components.tabs.tab.{tab}.schema.{field}.label` |
| Page title                       | `livewire/auth/login.title`                                              |
| Relation manager table           | `filament/resources/user-resource.relation_managers.posts.table.heading` |

Place strings under `lang/{locale}/` using nested arrays or dot keys.

### Example lang file

For a `UserResource` form with `name` and `email` fields, create
`lang/en/filament/resources/user-resource.php`:

```php
<?php

return [
    'model_label' => 'user',
    'plural_model_label' => 'users',
    'navigation_label' => 'Users',
    'navigation_group' => 'Access',

    'form' => [
        'name' => [
            'label' => 'Full name',
            'helper_text' => 'First and last name.',
        ],
        'email' => [
            'label' => 'Email address',
            'placeholder' => 'you@example.com',
        ],
    ],

    'table' => [
        'name' => ['label' => 'Name'],
        'email' => ['label' => 'Email'],
    ],
];
```

Any key you omit falls back to Filament's native label, so you only translate what you need.

## Component macros

Override or prefix translation keys on individual schema components:

```php
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

TextInput::make('name')
    ->conventionKey('custom.segment.name');

Toggle::make('active')
    ->conventionKey('globals.active', isAbsolute: true);
```

| Macro                       | Purpose                                              |
| --------------------------- | ---------------------------------------------------- |
| `conventionKey()`           | Set or derive the translation segment                |
| `getConventionKey()`        | Read the evaluated key                               |
| `conventionKeyAbsolute()`   | Mark the key as absolute (skip owner-path prefixing) |
| `isConventionKeyAbsolute()` | Check whether the key is absolute                    |

## Base classes

Extend Syriable’s translatable bases instead of Filament’s when you want convention-based metadata out of the box:

| Base class                         | Replaces                      |
| ---------------------------------- | ----------------------------- |
| `TranslatablePage`                 | `Filament\Pages\Page`         |
| `TranslatableResource`             | `Filament\Resources\Resource` |
| `TranslatableCluster`              | `Filament\Clusters\Cluster`   |
| `TranslatableCreateRecord`         | `CreateRecord`                |
| `TranslatableEditRecord`           | `EditRecord`                  |
| `TranslatableViewRecord`           | `ViewRecord`                  |
| `TranslatableListRecords`          | `ListRecords`                 |
| `TranslatableManageRecords`        | `ManageRecords`               |
| `TranslatableManageRelatedRecords` | `ManageRelatedRecords`        |
| `TranslatableRelationManager`      | `RelationManager`             |
| `TranslatableWidget`               | `Filament\Widgets\Widget`     |
| `TranslatableChartWidget`          | `ChartWidget`                 |
| `TranslatableTableWidget`          | `TableWidget`                 |
| `TranslatableStatsOverviewWidget`  | `StatsOverviewWidget`         |
| `TranslatableExporter`             | `Exporter`                    |
| `TranslatableImporter`             | `Importer`                    |

Example:

```php
use Syriable\Filament\Plugins\Translator\Filament\Pages\TranslatablePage;
use Syriable\Filament\Plugins\Translator\Filament\Resources\TranslatableResource;

class Login extends TranslatablePage { /* … */ }

class UserResource extends TranslatableResource { /* … */ }
```

## Traits

Use traits directly on your own classes when you prefer not to extend the base classes:

| Trait                           | Resolves                                                                        |
| ------------------------------- | ------------------------------------------------------------------------------- |
| `ResolvesPageLabels`            | Title, subheading, navigation label, navigation group                           |
| `ResolvesResourceLabels`        | Model label, plural model label, navigation label, navigation group, breadcrumb |
| `ResolvesResourcePageLabels`    | Resource page title, subheading, navigation metadata                            |
| `ResolvesRelationManagerLabels` | Relation manager title, model label, table/form/action labels                   |
| `ResolvesClusterLabels`         | Cluster navigation label and group                                              |
| `ResolvesWidgetLabels`          | Shared widget label resolution                                                  |
| `ResolvesChartWidgetLabels`     | Chart widget heading and description                                            |
| `ResolvesTableWidgetLabels`     | Table widget heading                                                            |
| `ResolvesStatsOverviewLabels`   | Stats overview heading and description                                          |
| `ResolvesExporterLabels`        | Exporter column labels                                                          |
| `ResolvesImporterLabels`        | Importer column labels                                                          |

Implement `TranslatesConventionally` when a class exposes `resolveLabel()` for custom convention lookups.

## Known limitations

- **[Hint-icon tooltips](#hint-icon-tooltips) need single-argument `->hintIcon($icon)`** and a Filament
  version that guards `hintIcon()` with `func_num_args()`; a second argument (or older Filament) bypasses
  the translation.
- **Custom table column types** are not auto-monitored (see [What gets translated](#what-gets-translated));
  use the `conventionKey()` macro for them. Custom *schema* components can be registered — see
  [Custom schema components](#custom-schema-components).
- **Nested modal action labels** are resolved by walking a parent action's *cached* and
  *extra-modal-footer* actions. To stay safe during action caching, discovery never evaluates an
  action's modal-action closures (which may type-hint a `Model $record` that is not yet available),
  so labels for **uncached** nested modal actions fall back to Filament's native label until the
  action is mounted/cached. Cached nested actions and extra modal footer actions resolve normally.

## Filament compatibility & upgrade strategy

This package integrates deeply with Filament internals to wire convention-based labels. To resolve
nested action, schema, table, and modal labels it uses `spatie/invade` and reflection against
Filament internals (for example `cachedActions` / `cachedModalActions` and prebuilt component
introspection), guarded by `class_exists()` / `method_exists()` / `ReflectionProperty::isInitialized()`
checks so missing internals degrade gracefully instead of throwing.

**Declared support:** `filament/filament: ^4.0 | ^5.0`.

Because the integration touches internals, a Filament **major** release (e.g. v6) carries a higher
upgrade risk than a typical dependency. Maintainer playbook for new Filament majors:

1. **Run the matrix first.** The test suite is the canary — every wired surface (actions, schemas,
   tables, filters, summarizers, widgets, importers/exporters, nested modal actions) has a feature
   test. Run it against the new Filament version before declaring support.
2. **Expect renames at the boundary.** Failures usually point at a renamed internal property or
   method reached via `invade()`/reflection. These are centralised in `ConventionRegistry`; the
   long-term direction is to isolate them behind small adapter helpers so version branches live in
   one place.
3. **Guard, don't assume.** New internals must be probed with `class_exists()` / `method_exists()` /
   reflection `isInitialized()` so the package keeps working when a hook is absent on a given
   version (this is how `Schemas\Components\Text` is handled across v4/v5).
4. **Widen the constraint deliberately.** Only bump the `filament/filament` constraint once the full
   suite is green on the new major, and record the change in the [CHANGELOG](CHANGELOG.md).

If you hit a label that stops resolving after a Filament upgrade, the native Filament label is used
as a fallback (nothing breaks visually) — please open an issue with the Filament version so the
matrix can be updated.

## Security considerations

This package has a deliberately small security surface — it resolves translation strings and, in
local development only, scaffolds missing keys. It performs no authorization, renders no HTML,
handles no HTTP input, and runs no database queries. Two things are worth calling out:

- **Lang files are trusted code.** Like all Laravel `lang/*.php` files, they are loaded with
  `require` and therefore execute as PHP. The dev-only key writer reads existing lang files via
  `require` when merging new keys, so treat lang files as source code from a trusted origin.
- **Key writing is production-gated.** `MissingTranslationKeyWriter` never writes on production
  requests regardless of configuration, and the resolved target path is validated to stay within
  `lang_path()` to prevent path traversal from a crafted convention key.

See [SECURITY.md](SECURITY.md) for the full security model and reporting policy.

## Architecture

| Concept        | Class / trait                 | Role                                                                       |
| -------------- | ----------------------------- | -------------------------------------------------------------------------- |
| Panel plugin   | `TranslatorPlugin`            | Registers the package on a panel; holds path aliases and key-creation opts |
| Label registry | `ConventionRegistry`          | Wires Filament `configureUsing` hooks; applies `required`/`components` config |
| Path aliases   | `ConfiguresPathAliases`       | Maps namespace fragments to lang file paths                                |
| Key creation   | `MissingTranslationKeyWriter` | Scaffolds missing required keys into lang files (local dev)                |
| Custom keys    | `conventionKey()` macro       | Override the derived key on any schema component                           |
| Macro storage  | `Support\ConventionKeyStore`  | `WeakMap`-backed store for macro key state (PHP 8.4 dynamic-property safe) |
| Internals      | `Support\FilamentInternals`   | Single adapter isolating `invade()`/reflection reads of Filament internals |

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

Please review [our security policy](https://github.com/syriable/filament-translator/security/policy) on how to report security vulnerabilities.

## Credits

- [Syriable](https://github.com/syriable)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
