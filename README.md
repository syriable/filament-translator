![Syriable Filament Translator](art/header-img.png)

# Syriable Filament Translator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/syriable/filament-translator.svg?style=flat-square)](https://packagist.org/packages/syriable/filament-translator)
[![GitHub Tests Action Status](https://github.com/syriable/filament-translator/actions/workflows/run-tests.yml/badge.svg)](https://github.com/syriable/filament-translator/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://github.com/syriable/filament-translator/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/syriable/filament-translator/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)

Convention-based automatic translations for [Filament](https://filamentphp.com) panels — forms, tables, actions, infolists, resources, pages, widgets, importers, and exporters.

**Syriable Filament Translator** derives translation keys from your PHP class names and Filament component names so UI code stays free of hard-coded copy. The package registers lazy label resolvers at boot; when a lang entry is missing, Filament’s default label is preserved.

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

Register `TranslatorPlugin` on a panel with `pathAliases()` when you need namespace remapping; aliases are read from the active plugin during label resolution.

## What gets translated

`ConventionRegistry` wires lazy resolvers through Filament’s `configureUsing` hooks. Missing translations fall back to Filament’s native labels.

| Area | Translated attributes |
|------|------------------------|
| **Actions** | Label, tooltip, badge, modal heading/description, submit/cancel labels, success/failure notification titles |
| **Forms & infolists** | Field labels, placeholders, helper text, hints, prefixes/suffixes, validation attributes, section headings/descriptions, wizard steps, repeater action labels, select create/edit modal headings, loading messages |
| **Tables** | Search placeholder, model labels, heading/description, default sort label, empty state heading/description, actions column label |
| **Columns** | Label, description, tooltip, prefix/suffix, placeholder, default value, validation attribute |
| **Filters** | Label, indicator, placeholder, true/false labels, constraint labels |
| **Summarizers & grouping** | Label, prefix, suffix, grouping labels |
| **Importers & exporters** | Column and action labels |

Static metadata on pages, resources, clusters, widgets, relation managers, and resource pages is resolved through the `Resolves*` traits (see below).

## Translation key convention

```
{owner-path}.{context}.{component-name}.{attribute}
```

Examples:

| UI source | Lang key |
|-----------|----------|
| `UserResource` form field `name` | `filament/resources/user-resource.form.name.label` |
| Login page action `login` | `livewire/auth/login.actions.login.label` |
| Page title | `livewire/auth/login.title` |
| Relation manager table | `filament/resources/user-resource.relation_managers.posts.table.heading` |

Place strings under `lang/{locale}/` using nested arrays or dot keys.

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

| Macro | Purpose |
|-------|---------|
| `conventionKey()` | Set or derive the translation segment |
| `getConventionKey()` | Read the evaluated key |
| `conventionKeyAbsolute()` | Mark the key as absolute (skip owner-path prefixing) |
| `isConventionKeyAbsolute()` | Check whether the key is absolute |

## Base classes

Extend Syriable’s translatable bases instead of Filament’s when you want convention-based metadata out of the box:

| Base class | Replaces |
|------------|----------|
| `TranslatablePage` | `Filament\Pages\Page` |
| `TranslatableResource` | `Filament\Resources\Resource` |
| `TranslatableCluster` | `Filament\Clusters\Cluster` |
| `TranslatableCreateRecord` | `CreateRecord` |
| `TranslatableEditRecord` | `EditRecord` |
| `TranslatableViewRecord` | `ViewRecord` |
| `TranslatableListRecords` | `ListRecords` |
| `TranslatableManageRecords` | `ManageRecords` |
| `TranslatableManageRelatedRecords` | `ManageRelatedRecords` |
| `TranslatableRelationManager` | `RelationManager` |
| `TranslatableWidget` | `Filament\Widgets\Widget` |
| `TranslatableChartWidget` | `ChartWidget` |
| `TranslatableTableWidget` | `TableWidget` |
| `TranslatableStatsOverviewWidget` | `StatsOverviewWidget` |
| `TranslatableExporter` | `Exporter` |
| `TranslatableImporter` | `Importer` |

Example:

```php
use Syriable\Filament\Plugins\Translator\Filament\Pages\TranslatablePage;
use Syriable\Filament\Plugins\Translator\Filament\Resources\TranslatableResource;

class Login extends TranslatablePage { /* … */ }

class UserResource extends TranslatableResource { /* … */ }
```

## Traits

Use traits directly on your own classes when you prefer not to extend the base classes:

| Trait | Resolves |
|-------|----------|
| `ResolvesPageLabels` | Title, subheading, navigation label, navigation group |
| `ResolvesResourceLabels` | Model label, plural model label, navigation label, navigation group, breadcrumb |
| `ResolvesResourcePageLabels` | Resource page title, subheading, navigation metadata |
| `ResolvesRelationManagerLabels` | Relation manager title, model label, table/form/action labels |
| `ResolvesClusterLabels` | Cluster navigation label and group |
| `ResolvesWidgetLabels` | Shared widget label resolution |
| `ResolvesChartWidgetLabels` | Chart widget heading and description |
| `ResolvesTableWidgetLabels` | Table widget heading |
| `ResolvesStatsOverviewLabels` | Stats overview heading and description |
| `ResolvesExporterLabels` | Exporter column labels |
| `ResolvesImporterLabels` | Importer column labels |

Implement `TranslatesConventionally` when a class exposes `resolveLabel()` for custom convention lookups.

## Architecture

| Concept | Class / trait | Role |
|--------|----------------|------|
| Panel plugin | `TranslatorPlugin` | Registers the package on a Filament panel and holds path aliases |
| Label registry | `ConventionRegistry` | Wires Filament `configureUsing` hooks for actions, schemas, tables |
| Path aliases | `ConfiguresPathAliases` | Maps namespace fragments to lang file paths |
| Custom keys | `conventionKey()` macro | Override the derived key on any schema component |

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
