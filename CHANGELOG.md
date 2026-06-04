# Changelog

All notable changes to `syriable/filament-translator` will be documented in this file.

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
