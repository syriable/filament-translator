# Changelog

All notable changes to `syriable/filament-translator` will be documented in this file.

## Unreleased

### Added

- **Automatic key creation (local development).** `TranslatorPlugin::make()->createMissingTranslationKeys()` scaffolds missing **required** convention keys into your lang files as you browse — creating the file, the nested array path, and a humanised default value (overridable via a closure) while preserving existing translations. Always skipped in production.
- **Configurable required attributes.** A publishable `config/filament-translator.php` with a `required` map to override which component attributes must be translated (for example make `placeholder` required, or `label` optional). Overrides apply across every context where the attribute appears.
- **Custom schema components.** Register your own schema components and their translatable attributes via `config('filament-translator.components')`, resolved through the same convention pipeline as first-party fields (#28).
- **`Filament\Schemas\Components\Text` support.** Address it with `Text::make(null)->key('…')` to resolve `content` from lang, while explicit content stays literal (#29).
- **Hint-icon tooltips.** Resolve `hintIconTooltip` from the `hint_icon_tooltip` convention key. Use single-argument `->hintIcon($icon)`; a second argument overrides the wired tooltip (#30).

### Fixed

- Tab convention keys no longer contain a duplicate `tabs` segment — tab items now resolve under `tabs.tab.{tab}.…` (#31).
- Drop the deprecated `Filament\Forms\Components\Placeholder` registration; `Placeholder` is still covered through the monitored `Infolists\Components\Entry` it extends (#25).

## 1.0.1 - 2026-06-04

First functional release: `1.0.0` shipped the rebrand but was unusable in production because its runtime dependencies were never declared. This release fixes that and hardens label resolution.

- Fix: declare `spatie/invade` and `filament/filament` as runtime dependencies (previously `invade()` was undefined in production).
- Fix: `ResolvesResourceLabels`, `ResolvesClusterLabels`, `ResolvesWidgetLabels`, `ResolvesImporterLabels`, and `ResolvesExporterLabels` resolved `TranslatorPlugin` against the wrong namespace, causing a fatal "class not found".
- Fix: remove the global `ini_set('max_execution_time', 5)` that capped every panel request to 5 seconds.
- Fix: `getCompletedNotificationBody()` on the importer and exporter no longer returns `null` against its `string` return type when translations are missing.
- Fix: `TranslatorPlugin::get()` tolerates the plugin not being registered on the active panel (standalone Livewire pages).
- Fix: parent-action discovery no longer evaluates uncached modal-action closures, preventing a crash when closures type-hint a not-yet-set record.
- Fix: correct the misused third argument of `Stringable::replace()` in `sanitizeComponentPath()`.
- Perf: memoize prebuilt action/filter instances and `translator()->has()` lookups during label resolution.
- Refactor: extract the shared convention-key derivation into `ResolvesConventionNamespace` to remove duplication across the `Resolves*` traits.
- Add a Pest/Testbench test suite (unit, feature, and resolver-engine coverage), `phpunit.xml.dist`, Larastan/PHPStan static analysis, a `LICENSE.md` file, and expanded documentation (example lang file, known limitations, monitored column types).

## 1.0.0 - 2026-06-03

Initial release of `syriable/filament-translator`, a rebrand of `ralphjsmit/laravel-filament-auto-translator`.

- Rebrand package from `ralphjsmit/laravel-filament-auto-translator` to `syriable/filament-translator`.
- Namespace changed to `Syriable\Filament\Plugins\Translator`.
- Renamed public API for Syriable branding: `ConventionRegistry`, `TranslatorPlugin`, `resolveLabel()`, `pathAliases()`, `conventionKey()`, `ResolvesPageLabels`, `TranslatablePage`, and related types.
