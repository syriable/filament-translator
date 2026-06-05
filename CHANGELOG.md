# Changelog

All notable changes to `syriable/filament-translator` will be documented in this file.

## 1.1.1 - 2026-06-05

### What's Changed

* Fix/issues batch 34 73 by @alkhatibsy in https://github.com/syriable/filament-translator/pull/74

**Full Changelog**: https://github.com/syriable/filament-translator/compare/1.1.0...1.1.1

## 1.1.0 - 2026-06-05

### What's Changed

* feat: automatic translation key path creation by @alkhatibsy in https://github.com/syriable/filament-translator/pull/26
* feat: configurable required translation attributes by @alkhatibsy in https://github.com/syriable/filament-translator/pull/27
* fix: resolve open schema-resolution issues (#25, #28–#31) by @alkhatibsy in https://github.com/syriable/filament-translator/pull/32
* docs: comprehensive README + CHANGELOG covering all features by @alkhatibsy in https://github.com/syriable/filament-translator/pull/33

**Full Changelog**: https://github.com/syriable/filament-translator/compare/1.0.1...1.1.0

## Unreleased

### Added

- **Automatic key creation (local development).** `TranslatorPlugin::make()->createMissingTranslationKeys()` scaffolds missing **required** convention keys into your lang files as you browse — creating the file, the nested array path, and a humanised default value (overridable via a closure) while preserving existing translations. Always skipped in production.
- **Configurable required attributes.** A publishable `config/filament-translator.php` with a `required` map to override which component attributes must be translated (for example make `placeholder` required, or `label` optional). Overrides apply across every context where the attribute appears.
- **Custom schema components.** Register your own schema components and their translatable attributes via `config('filament-translator.components')`, resolved through the same convention pipeline as first-party fields (#28).
- **`Filament\Schemas\Components\Text` support.** Address it with `Text::make(null)->key('…')` to resolve `content` from lang, while explicit content stays literal (#29).
- **Hint-icon tooltips.** Resolve `hintIconTooltip` from the `hint_icon_tooltip` convention key. Use single-argument `->hintIcon($icon)`; a second argument overrides the wired tooltip (#30).
- **`FilamentInternals` adapter.** All `invade()`/reflection access to Filament internals now lives behind a single adapter, isolating Filament-version coupling for safer major upgrades (#57).
- **`SECURITY.md`** documenting the package security model: no auth surface, dev-only/production-gated key writing, and the lang-file trust model (#51, #52).
- **Composer `analyse` script** running PHPStan with a 512M memory limit (#44), mirrored by the PHPStan CI workflow (#48).

### Changed

- **`registerDefaults()` is now idempotent.** It tracks the active component manager and a configuration signature, so duplicate panel/host boots and repeated Octane worker boots skip re-wiring while configuration changes still trigger re-registration (#38, #55).
- **PHPStan raised to level 3** with the dynamic-property suppressions removed (#35).
- **PHP 8.4 dynamic-property deprecations fixed** — `conventionKey` macro state is stored off-object in a `WeakMap`-backed `ConventionKeyStore` instead of dynamic properties (#36).
- Removed the unused `minimum-stability: dev` from `composer.json`, relying on `prefer-stable` (#43).
- Eloquent model casts are memoized for enum-backed option resolution (Radio, Select, ToggleButtons, SelectFilter), avoiding repeated model instantiation (#42).
- Documented Filament version compatibility for hint-icon tooltips, the double `registerDefaults()` pattern, standalone-Livewire path-alias behaviour, the Filament v6 upgrade strategy, and the reserved `InfolistScope` enum (#46, #47, #53, #54, #45).

### Fixed

- Tab convention keys no longer contain a duplicate `tabs` segment — tab items now resolve under `tabs.tab.{tab}.…` (#31).
- Drop the deprecated `Filament\Forms\Components\Placeholder` registration; `Placeholder` is still covered through the monitored `Infolists\Components\Entry` it extends (#25).
- Removed a duplicate `Tables\Grouping\Group::configureUsing` registration; grouping labels are wired once in `wireGroupingLabels()` (#39, #40).
- `MissingTranslationKeyWriter` validates the resolved lang path stays within `lang_path()`, preventing path traversal from a crafted convention key (#50).
- Implemented the relation-manager absolute-key path for mounted actions (#71).
- Removed the unused `ResolutionMode` enum (#37).

### Tests

- Added feature coverage for wizards/steps, repeater & builder nested blocks, infolist entries, summarizers, clusters, relation managers, widgets (chart/table/stats overview), resource & page metadata, importer/exporter column labels, nested modal action labels, the `Text` v4/v5 guard, `registerDefaults()` idempotency, and standalone-Livewire path-alias fallback (#58–#70, #72, #73, #49, #66, #67, #68, #69).

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
