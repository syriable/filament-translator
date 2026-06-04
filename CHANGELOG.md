# Changelog

All notable changes to `syriable/filament-translator` will be documented in this file.

## 1.0.1 - 2026-06-04

First functional release of the rebranded `syriable/filament-translator` package. `1.0.0` introduced the rebrand (from `ralphjsmit/laravel-filament-auto-translator`), the `Syriable\Filament\Plugins\Translator` namespace, and the renamed public API — but was unusable in production because its runtime dependencies were never declared. This release fixes that and hardens label resolution.

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

## 2.1.1 - 2026-02-02

- Fix: allow enum-based navigation groups.

## 1.4.2 - 2026-01-29

- Fix: infinite recursion in form component actions

## 2.1.0 - 2026-01-24

- Feat: Filament V5 support

## 2.0.2 - 2026-01-15

- Feat: support V4 resource structure

## 2.0.1 - 2026-01-10

- Fix: custom registered modal actions with records

## 2.0.0 - 2026-01-01

- Filament V4 support

## 1.4.1 - 2025-07-31

- Chore: make page translation context dynamic in default traits

## 1.2.1 - 2025-07-31

- Chore: make page translation context dynamic in default traits

## 1.4.0 - 2025-07-18

- Feat: add importer & exporter support

## 1.3.4 - 2025-06-26

- Fix: redirecting of translations on table action with absolute translation key on relation manager with name of `create`

## 1.3.3 - 2025-03-12

- Feat: add ability to redirect form container translations
- Feat: add support for table query builder constraints
- Fix: improve support for wizards in actions

## 1.3.2 - 2025-03-05

- Feat: support custom table filter forms.

## 1.3.1 - 2025-02-27

- Feat: improved detection of current component in wizard in action

## 1.3.0 - 2025-02-25

- Feat: Laravel 12 support.
- Fix: preserve form components behind modal background to get translated from action.

## 1.2.0 - 2025-02-17

- Feat: ability to have absolute translation keys.

## 1.1.11 - 2025-02-15

- Revert: optimize `->translationKey()` performance with many translation keys

## 1.1.10 - 2025-02-15

- Feat: optimize `->translationKey()` performance with many translation keys
- Feat: create widget-specific traits
- Test: action scenario with `ActionGroup`

## 1.1.9 - 2025-02-13

- Fix: access form name gracefully.

## 1.1.8 - 2025-02-11

- Chore: translate toggle & select column

## 1.1.7 - 2025-01-28

- Feat: add support for automatic translating of table groups after Filament closure support.
- Fix: change retrieving of pre-built translations for filters

## 1.1.5 - 2025-01-16

- Feat: add breadcrumb support

## 1.1.4 - 2025-01-16

- Fix: custom form names when provided by traits

## 1.1.3 - 2025-01-16

- Fix: apply default filter actions text.

## 1.1.2 - 2025-01-16

- Fix: non-table actions with forms on `ManageRelatedRecords` page.
- Fix: correctly apply default bulk actions text.

## 1.1.1 - 2025-01-16

- Fix: `Argument #2 ($record) must be of type Illuminate\Database\Eloquent\Model, null given`

## 1.1.0 - 2025-01-15

- Feat: add support for table `ColumnGroup` components.
- Fix: use of default table filter.

## 1.0.0 - 2025-01-01

- Initial release!
