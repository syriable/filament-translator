<?php

namespace Syriable\Filament\Plugins\Translator;

use BackedEnum;
use Closure;
use Countable;
use Filament\Actions;
use Filament\Forms;
use Filament\Infolists;
use Filament\Resources;
use Filament\Schemas;
use Filament\Support\Contracts\HasDescription;
use Filament\Tables;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use Livewire\Livewire;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;
use Syriable\Filament\Plugins\Translator\Enums\ActionScope;
use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use Syriable\Filament\Plugins\Translator\Enums\SchemaScope;
use Syriable\Filament\Plugins\Translator\Enums\TableScope;
use ReflectionProperty;

/**
 * Central registry that wires Filament component defaults to convention-based lang lookups.
 *
 * On {@see registerDefaults()}, the registry attaches lazy label resolvers to actions, schema
 * fields, tables, filters, importers, and exporters. Each resolver builds a dotted key from
 * the Livewire owner class and component name, then falls back to Filament's native label
 * when no translation exists.
 */
class ConventionRegistry
{
    protected array $actionLabelAttributes = [
        'label' => false,
        'tooltip' => true,
        'badge' => true,
        'modalHeading' => true,
        'modalDescription' => true,
        'modalSubmitActionLabel' => true,
        'modalCancelActionLabel' => true,
        'successNotificationTitle' => true,
        'failureNotificationTitle' => true,
    ];

    /**
     * @var array<array-key, class-string<Schemas\Components\Component>>
     */
    protected array $monitoredSchemaTypes = [
        Schemas\Components\Fieldset::class,
        Schemas\Components\Section::class,
        Schemas\Components\Tabs\Tab::class,
        Schemas\Components\Wizard::class,
        Schemas\Components\Wizard\Step::class,
        Forms\Components\Field::class,
        Forms\Components\Placeholder::class,
        Infolists\Components\Entry::class,
    ];

    /**
     * Provide the schema component methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $schemaLabelAttributes = [
        'label' => false,
        'placeholder' => true,
        'helperText' => true,
        'hint' => true,
        // 'hintIconTooltip' => true, // Note: doesn't work, calling the `->hintIcon()` method overwrites the `->hintIconTooltip()`.
        'prefix' => true,
        'suffix' => true,
        'validationAttribute' => true,
        'addActionLabel' => true, // Repeater
        'addBetweenActionLabel' => true, // Repeater
        'heading' => false, // Section
        'description' => true, // Section, Wizard\Step
        'content' => false, // Placeholder
        'loadingMessage' => true, // Select
        'createOptionModalHeading' => true, // Select
        'editOptionModalHeading' => true, // Select
    ];

    /**
     * Provide the table methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableLabelAttributes = [
        'searchPlaceholder' => true,
        'modelLabel' => true,
        'pluralModelLabel' => true,
        'heading' => true,
        'description' => true,
        'defaultSortOptionLabel' => true,
        'emptyStateHeading' => true,
        'emptyStateDescription' => true,
        'actionsColumnLabel' => true,
    ];

    /**
     * @var array<array-key, class-string<Tables\Columns\Column>>
     */
    protected array $monitoredColumnTypes = [
        Tables\Columns\TextColumn::class,
        Tables\Columns\IconColumn::class,
        Tables\Columns\ColorColumn::class,
        Tables\Columns\ToggleColumn::class,
        Tables\Columns\SelectColumn::class,
    ];

    /**
     * Provide the table column methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $columnLabelAttributes = [
        'label' => false,
        'description' => true,
        'tooltip' => true,
        'prefix' => true,
        'suffix' => true,
        'placeholder' => true,
        'default' => true,
        'validationAttribute' => true,
    ];

    /**
     * Provide the table filter methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $filterLabelAttributes = [
        'label' => false,
        'indicator' => true,
        'placeholder' => true,
        'trueLabel' => true,
        'falseLabel' => true,
    ];

    /**
     * Provide the table filter constraint methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $tableFilterConstraintMethods = [
        'label' => false,
        'attributeLabel' => true,
    ];

    /**
     * Provide the table summarizer methods to check and whether to allow null values.
     *
     * @var array<string, bool>
     */
    protected array $summarizerLabelAttributes = [
        'label' => false,
        'prefix' => true,
        'suffix' => true,
    ];

    /**
     * Cached prebuilt action/filter instances used to read framework default labels,
     * keyed by "{class}|{name}" so we instantiate + set up each one at most once per request.
     *
     * @var array<string, object>
     */
    protected static array $prebuiltComponentCache = [];

    /**
     * Memoized `translator()->has()` results, keyed by "{locale}|{key}".
     *
     * @var array<string, bool>
     */
    protected static array $translatorHasCache = [];

    public function registerDefaults(): void
    {
        // Actions:
        $this->wireActionLabels();
        $this->wireExporterLabels();
        $this->wireImporterLabels();

        // Schemas:
        $this->wireSchemaLabels();

        // Tables:
        $this->wireTableLabels();
        $this->wireColumnLabels();
        $this->wireSummarizerLabels();
        $this->wireFilterLabels();
        $this->wireGroupingLabels();
    }

    protected function wireActionLabels(): void
    {
        $actionLabelAttributes = $this->actionLabelAttributes;

        Actions\Action::configureUsing(static function (Actions\Action $action) use ($actionLabelAttributes) {
            foreach ($actionLabelAttributes as $method => $allowNull) {
                $action->{$method}(static function (Actions\Action $action) use ($method, $allowNull) {
                    $actionText = ConventionRegistry::resolveActionLabel($action, null, Str::snake($method), allowNull: true);

                    if ($actionText) {
                        return $actionText;
                    }

                    // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                    if ($action::class !== Actions\Action::class) {
                        $prebuiltAction = ConventionRegistry::prebuiltComponent($action::class, $action->getName());

                        $default = invade($prebuiltAction)->{Str::camel($method)};

                        return $action->evaluate($default instanceof Closure ? Closure::bind($default, $action) : $default);
                    }

                    if ($allowNull) {
                        return $actionText;
                    }

                    return ConventionRegistry::resolveActionLabel($action, null, Str::snake($method), allowNull: false);
                });
            }
        }, isImportant: true);
    }

    protected function wireExporterLabels(): void
    {
        Actions\Exports\ExportColumn::configureUsing(static function (Actions\Exports\ExportColumn $column) {
            return $column->label(static function (Actions\Exports\ExportColumn $column, ?Actions\Exports\Exporter $exporter) {
                if (! $exporter) {
                    $livewire = Livewire::current();

                    if (! $livewire) {
                        return null;
                    }

                    // Note: do not use `getMountedAction()`, as that will cause an infinite loop
                    // when a translation is requested *during* the caching of an action.
                    if ($livewire instanceof Actions\Contracts\HasActions) {
                        foreach (invade($livewire)->cachedMountedActions ?? [] as $cachedMountedAction) {
                            if ($cachedMountedAction instanceof Actions\ExportAction || $cachedMountedAction instanceof Tables\Actions\ExportAction) {
                                $exporter = $cachedMountedAction->getExporter();

                                break;
                            }
                        }
                    }
                }

                if (! $exporter) {
                    return null;
                }

                if (! (class_implements($exporter)[TranslatesConventionally::class] ?? null)) {
                    return null;
                }

                $normalizedName = ConventionRegistry::sanitizeComponentPath($column->getName());

                return $exporter::resolveLabel("columns.{$normalizedName}.label", allowNull: false);
            });
        }, isImportant: true);
    }

    protected function wireImporterLabels(): void
    {
        Actions\Imports\ImportColumn::configureUsing(static function (Actions\Imports\ImportColumn $column) {
            return $column
                ->exampleHeader(static function (Actions\Imports\ImportColumn $column, ?Actions\Imports\Importer $importer) {
                    if (! $importer) {
                        $livewire = Livewire::current();

                        if (! $livewire) {
                            return null;
                        }

                        // Note: do not use `getMountedAction()`, as that will cause an infinite loop
                        // when a translation is requested *during* the caching of an action.
                        if ($livewire instanceof Actions\Contracts\HasActions) {
                            foreach (invade($livewire)->cachedMountedActions ?? [] as $cachedMountedAction) {
                                if ($cachedMountedAction instanceof Actions\ImportAction || $cachedMountedAction instanceof Tables\Actions\ImportAction) {
                                    $importer = $cachedMountedAction->getImporter();

                                    break;
                                }
                            }
                        }
                    }

                    if (! $importer) {
                        return null;
                    }

                    if (! (class_implements($importer)[TranslatesConventionally::class] ?? null)) {
                        return null;
                    }

                    $normalizedName = ConventionRegistry::sanitizeComponentPath($column->getName());

                    return $importer::resolveLabel("columns.{$normalizedName}.example_header", allowNull: true);
                })
                ->label(static function (Actions\Imports\ImportColumn $column, ?Actions\Imports\Importer $importer) {
                    if (! $importer) {
                        $livewire = Livewire::current();

                        if (! $livewire) {
                            return null;
                        }

                        // Note: do not use `getMountedAction()`, as that will cause an infinite loop
                        // when a translation is requested *during* the caching of an action.
                        if ($livewire instanceof Actions\Contracts\HasActions) {
                            foreach (invade($livewire)->cachedMountedActions ?? [] as $cachedMountedAction) {
                                if ($cachedMountedAction instanceof Actions\ImportAction || $cachedMountedAction instanceof Tables\Actions\ImportAction) {
                                    $importer = $cachedMountedAction->getImporter();

                                    break;
                                }
                            }
                        }
                    }

                    if (! $importer) {
                        return null;
                    }

                    if (! (class_implements($importer)[TranslatesConventionally::class] ?? null)) {
                        return null;
                    }

                    $normalizedName = ConventionRegistry::sanitizeComponentPath($column->getName());

                    return $importer::resolveLabel("columns.{$normalizedName}.label", allowNull: false);
                });
        }, isImportant: true);
    }

    protected function wireSchemaLabels(): void
    {
        // Keep this configuration before the `foreach` loop, to ensure that the configuration callbacks are called first and not overridden.
        Schemas\Components\Fieldset::configureUsing(static function (Schemas\Components\Fieldset $component) {
            // Calling the `getLabel()` method whilst it is a closure will cause an error about the $livewire component not being set.
            // If we were to not check for the closure, we might break existing forms in the app that aren't using autotranslator.
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key($component->getLabel());
        });

        Schemas\Components\Section::configureUsing(static function (Schemas\Components\Section $component) {
            if (invade($component)->heading instanceof Closure) {
                return $component;
            }

            $heading = $component->getHeading();

            if (blank($heading)) {
                return;
            }

            return $component->key($heading);
        });

        Schemas\Components\Tabs\Tab::configureUsing(static function (Schemas\Components\Tabs\Tab $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->conventionKey($component->getLabel());
        });

        Schemas\Components\Wizard\Step::configureUsing(static function (Schemas\Components\Wizard\Step $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            return $component->key(invade($component)->label);
        });

        foreach ($this->monitoredSchemaTypes as $schemaComponent) {
            if (! class_exists($schemaComponent)) {
                continue;
            }

            $schemaLabelAttributes = $this->schemaLabelAttributes;

            $schemaComponent::configureUsing(static function (Schemas\Components\Component $component) use ($schemaLabelAttributes) {
                foreach ($schemaLabelAttributes as $method => $allowNull) {
                    if (
                        method_exists($component, $method)
                    ) {
                        if (
                            $component instanceof Schemas\Components\Section && $method === 'label'
                        ) {
                            continue;
                        }

                        $component->{$method}(static function (Schemas\Components\Component $component) use ($method, $allowNull) {
                            return ConventionRegistry::resolveSchemaLabel($component, SchemaScope::Components, Str::snake($method), allowNull: $allowNull);
                        });
                    }
                }
            });
        }

        Forms\Components\Radio::configureUsing(static function (Forms\Components\Radio $component) {
            $component
                ->options(static function (Forms\Components\Radio $component, string $model) {
                    /** @var class-string<Model> $cast */
                    $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                    if (! $cast) {
                        return null;
                    }

                    if (str($cast)->startsWith(AsEnumCollection::class)) {
                        $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                    }

                    if (! is_subclass_of($cast, BackedEnum::class)) {
                        return null;
                    }

                    return $cast;
                })
                ->descriptions(static function (Forms\Components\Radio $component, ?string $model) {
                    if (! $model) {
                        return [];
                    }

                    /** @var class-string<Model> $cast */
                    $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                    if (! $cast) {
                        return [];
                    }

                    if (str($cast)->startsWith(AsEnumCollection::class)) {
                        $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                    }

                    if (! is_subclass_of($cast, BackedEnum::class)) {
                        return [];
                    }

                    if (! is_a($cast, HasDescription::class, true)) {
                        return [];
                    }

                    return array_reduce($cast::cases(), function (array $carry, HasDescription&BackedEnum $case): array {
                        if (filled($description = $case->getDescription())) {
                            $carry[$case->value ?? $case->name] = $description;
                        }

                        return $carry;
                    }, []);
                });
        });

        Forms\Components\Select::configureUsing(static function (Forms\Components\Select $component) {
            $component->options(static function (Forms\Components\Select $component, string $model) {
                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                if (! $cast) {
                    return null;
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return null;
                }

                return $cast;
            });
        });

        Forms\Components\ToggleButtons::configureUsing(static function (Forms\Components\ToggleButtons $component) {
            $component->options(static function (Forms\Components\ToggleButtons $component, string $model) {
                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$component->getName()] ?? null;

                if (! $cast) {
                    return null;
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return null;
                }

                return $cast;
            });
        });
    }

    protected function wireTableLabels(): void
    {
        $tableLabelAttributes = $this->tableLabelAttributes;

        Tables\Table::configureUsing(static function (Tables\Table $table) use ($tableLabelAttributes) {
            foreach ($tableLabelAttributes as $method => $allowNull) {
                $table->{$method}(static function (Tables\Table $table) use ($allowNull, $method) {
                    return ConventionRegistry::resolveTableLabel($table, null, Str::snake($method), allowNull: $allowNull);
                });
            }
        }, isImportant: true);
    }

    protected function wireColumnLabels(): void
    {
        Tables\Columns\ColumnGroup::configureUsing(static function (Tables\Columns\ColumnGroup $component) {
            if (invade($component)->label instanceof Closure) {
                return $component;
            }

            $label = $component->getLabel();

            if (blank($label)) {
                return;
            }

            return $component->conventionKey($label);
        });

        $columnLabelAttributes = $this->columnLabelAttributes;

        foreach ($this->monitoredColumnTypes as $tableColumn) {
            if (! class_exists($tableColumn)) {
                continue;
            }

            $tableColumn::configureUsing(static function (Tables\Columns\Column|Tables\Columns\ColumnGroup $column) use ($columnLabelAttributes) {
                foreach ($columnLabelAttributes as $method => $allowNull) {
                    if (method_exists($column, $method)) {
                        $column->{$method}(static function (Tables\Columns\Column $column) use ($method, $allowNull) {
                            return ConventionRegistry::resolveTableLabel($column, TableScope::Columns, Str::snake($method), allowNull: $allowNull);
                        });
                    }
                }
            });
        }

        Tables\Columns\ColumnGroup::configureUsing(static function (Tables\Columns\ColumnGroup $group) use ($columnLabelAttributes) {
            foreach ($columnLabelAttributes as $method => $allowNull) {
                if (method_exists($group, $method)) {
                    $group->{$method}(static function (Tables\Columns\ColumnGroup $column) use ($method, $allowNull) {
                        return ConventionRegistry::resolveTableLabel($column, TableScope::Columns, Str::snake($method), allowNull: $allowNull);
                    });
                }
            }
        });
    }

    protected function wireFilterLabels(): void
    {
        $filterLabelAttributes = $this->filterLabelAttributes;

        Tables\Filters\BaseFilter::configureUsing(static function (Tables\Filters\BaseFilter $filter) use ($filterLabelAttributes) {
            foreach ($filterLabelAttributes as $method => $allowNull) {
                if (method_exists($filter, $method)) {
                    $filter->{$method}(static function (Tables\Filters\BaseFilter $filter) use ($method, $allowNull) {
                        $filterText = ConventionRegistry::resolveTableLabel($filter, TableScope::Filters, Str::snake($method), allowNull: true);

                        if ($filterText) {
                            return $filterText;
                        }

                        $shouldRetrievePrebuiltTranslation = ($filter instanceof Tables\Filters\SelectFilter && $method === 'placeholder')
                            || ($filter instanceof Tables\Filters\TernaryFilter && in_array($method, ['trueLabel', 'falseLabel', 'placeholder']))
                            || $filter instanceof Tables\Filters\TrashedFilter;

                        // Check if the action is a pre-built action, so we can then bind to the pre-built action's method.
                        if ($shouldRetrievePrebuiltTranslation) {
                            $prebuiltFilter = ConventionRegistry::prebuiltComponent($filter::class, $filter->getName());

                            $default = invade($prebuiltFilter)->{Str::camel($method)};

                            return $filter->evaluate($default instanceof Closure ? Closure::bind($default, $filter) : $default);
                        }

                        if ($allowNull) {
                            return $filterText;
                        }

                        return ConventionRegistry::resolveTableLabel($filter, TableScope::Filters, Str::snake($method), allowNull: false);
                    });
                }
            }
        }, isImportant: true);

        Tables\Filters\SelectFilter::configureUsing(static function (Tables\Filters\SelectFilter $filter) {
            $filter->options(static function (Tables\Table $table, Tables\Filters\SelectFilter $filter) {
                $model = $table->getModel();

                /** @var class-string<Model> $cast */
                $cast = (new $model)->getCasts()[$filter->getName()] ?? null;

                if (! $cast) {
                    return [];
                }

                if (str($cast)->startsWith(AsEnumCollection::class)) {
                    $cast = str($cast)->after(AsEnumCollection::class.':')->toString();
                }

                if (! is_subclass_of($cast, BackedEnum::class)) {
                    return [];
                }

                return $cast;
            });
        });

        $tableFilterConstraintMethods = $this->tableFilterConstraintMethods;

        Tables\Filters\QueryBuilder\Constraints\Constraint::configureUsing(static function (Tables\Filters\QueryBuilder\Constraints\Constraint $constraint) use ($tableFilterConstraintMethods) {
            foreach ($tableFilterConstraintMethods as $method => $allowNull) {
                if (method_exists($constraint, $method)) {
                    $constraint->{$method}(static function (Tables\Filters\QueryBuilder\Constraints\Constraint $constraint) use ($method, $allowNull) {
                        /** @var Tables\Filters\QueryBuilder $filter */
                        $filter = $constraint->getFilter();

                        return ConventionRegistry::resolveTableLabel($filter, TableScope::Filters, "constraints.{$constraint->getName()}.".Str::snake($method), allowNull: $allowNull);
                    });
                }
            }
        }, isImportant: true);
    }

    protected function wireGroupingLabels(): void
    {
        Tables\Grouping\Group::configureUsing(static function (Tables\Grouping\Group $group) {
            $group->label(static function (Tables\Grouping\Group $group) {
                return ConventionRegistry::resolveTableLabel($group, TableScope::Groups, 'label');
            });
        }, isImportant: true);
    }

    protected function wireSummarizerLabels(): void
    {
        $summarizerLabelAttributes = $this->summarizerLabelAttributes;

        Tables\Columns\Summarizers\Summarizer::configureUsing(static function (Tables\Columns\Summarizers\Summarizer $summarizer) use ($summarizerLabelAttributes) {
            foreach ($summarizerLabelAttributes as $method => $allowNull) {
                $summarizer->{$method}(static function (Tables\Columns\Summarizers\Summarizer $summarizer) use ($method, $allowNull) {
                    return ConventionRegistry::resolveTableLabel($summarizer, TableScope::Filters, Str::snake($method), allowNull: $allowNull);
                });
            }
        }, isImportant: true);

        Tables\Grouping\Group::configureUsing(static function (Tables\Grouping\Group $group) {
            $group->label(static function (Tables\Grouping\Group $group) {
                return ConventionRegistry::resolveTableLabel($group, TableScope::Groups, 'label');
            });
        }, isImportant: true);
    }

    /**
     * Resolve (and memoize) a prebuilt action/filter instance so its framework default labels
     * can be read without re-instantiating and re-running `setUp()` for every label attribute.
     */
    protected static function prebuiltComponent(string $class, string $name): object
    {
        $cacheKey = $class.'|'.$name;

        return static::$prebuiltComponentCache[$cacheKey] ??= tap(
            app($class, ['name' => $name]),
            static fn (object $component) => invade($component)->setUp(),
        );
    }

    protected static function translatorHas(string $key): bool
    {
        $cacheKey = app()->getLocale().'|'.$key;

        return static::$translatorHasCache[$cacheKey] ??= app('translator')->has($key);
    }

    protected static function sanitizeComponentPath(string $name, array $namespace = []): string
    {
        if ($namespace) {
            $prefix = implode('.', $namespace).'.';
        } else {
            $prefix = '';
        }

        return str($name)
            ->whenStartsWith('data.', static function (Stringable $normalizedName) {
                return $normalizedName->after('data.');
            })
            ->whenStartsWith('form.', static function (Stringable $normalizedName) {
                return $normalizedName->after('form.');
            })
            ->whenStartsWith('schema.', static function (Stringable $normalizedName) {
                return $normalizedName->after('schema.');
            })
            ->whenStartsWith('mountedActionSchema', static function (Stringable $normalizedName) {
                return $normalizedName->after('.'); // Strip until after `mountedActionSchema{index}.`
            })
            ->replace('.', '->')
            ->prepend($prefix);
    }

    protected static function lookupAbsoluteKey(string $topLevel, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        if ($topLevel) {
            $conventionKey = "{$topLevel}.{$key}";
        } else {
            $conventionKey = $key;
        }

        if (! static::translatorHas($conventionKey) && ($allowNull || app()->isProduction())) {
            return null;
        }

        if ($number !== null) {
            return trans_choice($conventionKey, $number, $replace);
        }

        return __($conventionKey, $replace);
    }

    public static function resolveActionLabel(Actions\Action $actionComponent, ?ActionScope $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $actionComponent->getLivewire();

        if (! $livewire instanceof TranslatesConventionally) {
            return null;
        }

        if (
            $actionComponent->isConventionKeyAbsolute()
            && ($conventionKey = $actionComponent->getConventionKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return ConventionRegistry::lookupAbsoluteKey($conventionKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = ConventionRegistry::sanitizeComponentPath($actionComponent->getConventionKey() ?? $actionComponent->getName());

        $parentComponent = $actionComponent->getSchemaContainer()?->getParentComponent() ?? $actionComponent->getSchemaComponent();

        if ($parentComponent instanceof Schemas\Components\Actions) {
            return ConventionRegistry::resolveSchemaLabel($parentComponent, SchemaScope::Components, $group ? "actions.{$normalizedName}.{$group->value}.{$key}" : "actions.{$normalizedName}.{$key}", allowNull: $allowNull);
        } elseif ($parentComponent instanceof Schemas\Components\Component) {
            $formActionTypeNamespace = null;

            $formActionTypes = [
                'prefix_actions',
                'suffix_actions',
                'extra_item_actions',
                'footer_actions',
                'header_actions',
                'hint_actions',
            ];

            foreach ($formActionTypes as $type) {
                $method = 'get'.Str::studly($type);

                if (
                    method_exists($parentComponent, $method)
                    && collect($parentComponent->{'get'.Str::studly($type)}())
                        ->map(static function (Actions\Action $action) {
                            return $action->getName();
                        })
                        ->contains($actionComponent->getName())
                ) {
                    $formActionTypeNamespace = $type;
                }
            }

            // If no form action type namespace was found, default to the `actions` namespace.
            // For example, in a custom component where actions are added, this will just
            // generate a key like "component_name.actions.action_name.action_key" fine.
            $formActionTypeNamespace ??= 'actions';

            return ConventionRegistry::resolveSchemaLabel($parentComponent, SchemaScope::Components, $group ? "{$formActionTypeNamespace}.{$normalizedName}.{$group->value}.{$key}" : "{$formActionTypeNamespace}.{$normalizedName}.{$key}", allowNull: $allowNull);
        }

        if (
            $livewire instanceof Tables\Contracts\HasTable
            && ($table = $actionComponent->getTable())
        ) {
            if ($actionComponent->isBulk()) {
                // Bulk actions are not used in nesting, so we can re-target them right away.
                return ConventionRegistry::resolveTableLabel($table, TableScope::BulkActions, $group ? "{$normalizedName}.{$group->value}.{$key}" : "{$normalizedName}.{$key}", $replace, $number, $allowNull);
            }

            /**
             * @return array{0: Actions\Action, 1: string}|null Returns [parentAction, keyPrefix] or null
             */
            $findParentActionCallback = function (Actions\Action $action, string $actionComponentName) use (&$findParentActionCallback): ?array {
                $extraModalFooterActionNames = [];

                foreach ($action->getExtraModalFooterActions() as $extraModalFooterAction) {
                    $extraModalFooterActionNames[] = $extraModalFooterAction->getName();

                    if ($extraModalFooterAction->getName() === $actionComponentName) {
                        return [$action, 'extra_modal_footer_actions'];
                    }

                    if ($result = $findParentActionCallback($extraModalFooterAction, $actionComponentName)) {
                        return $result;
                    }
                }

                // Note: `getModalActions()` includes `getModalFooterActions()` (submit, extra modal footer, cancel)
                // and `registerModalActions()`. We need to skip both the extra modal footer actions (already
                // checked above) and the built-in modal actions (submit, cancel) to avoid infinite recursion.
                $builtInModalActionNames = ['submit', 'cancel'];

                // Only iterate through modal actions if they are already cached. Calling `getModalActions()`
                // would trigger closure evaluation, which may fail if closures have type-hinted parameters
                // (like `Booking $record`) that would be null when the action doesn't have a record set yet.
                $cachedModalActionsProperty = new ReflectionProperty($action, 'cachedModalActions');
                $cachedModalActionsProperty->setAccessible(true);

                if ($cachedModalActionsProperty->isInitialized($action)) {
                    foreach ($cachedModalActionsProperty->getValue($action) ?? [] as $modalAction) {
                        if (in_array($modalAction->getName(), $extraModalFooterActionNames, true)) {
                            continue;
                        }

                        if (in_array($modalAction->getName(), $builtInModalActionNames, true)) {
                            continue;
                        }

                        if ($modalAction->getName() === $actionComponentName) {
                            return [$action, 'modal_actions'];
                        }

                        if ($result = $findParentActionCallback($modalAction, $actionComponentName)) {
                            return $result;
                        }
                    }
                }

                return null;
            };

            // Search for parent actions in table record actions. Modal actions (registered via
            // `registerModalActions()`) may not have a direct record association but their parent
            // action does, so we always search regardless of whether the action has a record.
            foreach ($table->getFlatRecordActions() as $action) {
                if ($action->getName() === $actionComponent->getName()) {
                    return ConventionRegistry::resolveTableLabel(
                        $table,
                        TableScope::Actions,
                        $group ? "{$normalizedName}.{$group->value}.{$key}" : "{$normalizedName}.{$key}",
                        $replace,
                        $number,
                        $allowNull
                    );
                }

                $result = $findParentActionCallback($action, $actionComponent->getName());

                if ($result !== null) {
                    [$parentAction, $keyPrefix] = $result;

                    return ConventionRegistry::resolveActionLabel($parentAction, null, $group ? "{$keyPrefix}.{$normalizedName}.{$group->value}.{$key}" : "{$keyPrefix}.{$normalizedName}.{$key}", $replace, $number, $allowNull);
                }
            }

            return ConventionRegistry::resolveTableLabel($table, TableScope::Actions, $group ? "{$normalizedName}.{$group->value}.{$key}" : "{$normalizedName}.{$key}", $replace, $number, $allowNull);
        }

        if (($parentComponent = $actionComponent->getSchemaContainer()?->getParentComponent()) instanceof Schemas\Components\Actions) {
            return ConventionRegistry::resolveSchemaLabel($parentComponent, SchemaScope::Components, "actions.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        // Check if this action is an extraModalFooterAction or modalAction of a page action
        /** @var Actions\Contracts\HasActions&Actions\Concerns\InteractsWithActions $livewire */
        /**
         * @return array{0: Actions\Action, 1: string}|null Returns [parentAction, keyPrefix] or null
         */
        $findParentActionCallback = function (Actions\Action $action, string $actionComponentName) use (&$findParentActionCallback): ?array {
            $extraModalFooterActionNames = [];

            foreach ($action->getExtraModalFooterActions() as $extraModalFooterAction) {
                $extraModalFooterActionNames[] = $extraModalFooterAction->getName();

                if ($extraModalFooterAction->getName() === $actionComponentName) {
                    return [$action, 'extra_modal_footer_actions'];
                }

                if ($result = $findParentActionCallback($extraModalFooterAction, $actionComponentName)) {
                    return $result;
                }
            }

            // Note: `getModalActions()` includes `getModalFooterActions()` (submit, extra modal footer, cancel)
            // and `registerModalActions()`. We need to skip both the extra modal footer actions (already
            // checked above) and the built-in modal actions (submit, cancel) to avoid infinite recursion.
            $builtInModalActionNames = ['submit', 'cancel'];

            // Only iterate through modal actions if they are already cached. Calling `getModalActions()`
            // would trigger closure evaluation, which may fail if closures have type-hinted parameters
            // (like `Booking $record`) that would be null when the action doesn't have a record set yet.
            $cachedModalActionsProperty = new ReflectionProperty($action, 'cachedModalActions');
            $cachedModalActionsProperty->setAccessible(true);

            if ($cachedModalActionsProperty->isInitialized($action)) {
                foreach ($cachedModalActionsProperty->getValue($action) ?? [] as $modalAction) {
                    if (in_array($modalAction->getName(), $extraModalFooterActionNames, true)) {
                        continue;
                    }

                    if (in_array($modalAction->getName(), $builtInModalActionNames, true)) {
                        continue;
                    }

                    if ($modalAction->getName() === $actionComponentName) {
                        return [$action, 'modal_actions'];
                    }

                    if ($result = $findParentActionCallback($modalAction, $actionComponentName)) {
                        return $result;
                    }
                }
            }

            return null;
        };

        $cachedActions = invade($livewire)->cachedActions;

        foreach ($cachedActions as $action) {
            if ($action->getName() === $actionComponent->getName()) {
                // This is a direct page action, let it fall through to the normal handling
                break;
            }

            $result = $findParentActionCallback($action, $actionComponent->getName());

            if ($result !== null) {
                [$parentAction, $keyPrefix] = $result;

                return ConventionRegistry::resolveActionLabel($parentAction, null, $group ? "{$keyPrefix}.{$normalizedName}.{$group->value}.{$key}" : "{$keyPrefix}.{$normalizedName}.{$key}", $replace, $number, $allowNull);
            }
        }

        return $livewire::resolveLabel($group ? "{$normalizedName}.{$group->value}.{$key}" : "{$normalizedName}.{$key}", replace: $replace, number: $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Actions);
    }

    public static function resolveSchemaLabel(Schemas\Components\Component|Schemas\Schema $schemaComponent, ?SchemaScope $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $schemaComponent->getLivewire();

        if (! $livewire instanceof TranslatesConventionally) {
            return null;
        }

        if (
            $schemaComponent->isConventionKeyAbsolute()
            && ($conventionKey = $schemaComponent->getConventionKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return ConventionRegistry::lookupAbsoluteKey($conventionKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = match (true) {
            $schemaComponent instanceof Forms\Components\Field => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? ($schemaComponent->evaluate(invade($schemaComponent)->key) ? $schemaComponent->getKey(isAbsolute: false) : $schemaComponent->getName())),
            $schemaComponent instanceof Forms\Components\Placeholder => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? $schemaComponent->getKey(isAbsolute: false) ?? $schemaComponent->getName()),
            $schemaComponent instanceof Schemas\Components\Tabs => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? 'tabs'),
            $schemaComponent instanceof Schemas\Components\Tabs\Tab => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? $schemaComponent->getKey(isAbsolute: false) ?? $schemaComponent->getId()),
            $schemaComponent instanceof Schemas\Components\Wizard => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? 'wizard'), // Do not include key or ID because the key or ID is based on state path and label, giving infinite recursion...
            $schemaComponent instanceof Schemas\Components\Wizard\Step => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? $schemaComponent->getKey(isAbsolute: false)),
            $schemaComponent instanceof Forms\Components\Builder\Block => ConventionRegistry::sanitizeComponentPath($schemaComponent->getConventionKey() ?? $schemaComponent->getKey(isAbsolute: false) ?? $schemaComponent->getName()),
            $schemaComponent instanceof Schemas\Schema => null,
            default => ($identifier = $schemaComponent->getConventionKey() ?? $schemaComponent->getKey(isAbsolute: false) ?? $schemaComponent->getId()) ? ConventionRegistry::sanitizeComponentPath($identifier) : null,
        };

        if (
            ! $normalizedName
            && $schemaComponent instanceof Schemas\Components\Component
            && ($name = $schemaComponent->getConventionKey() ?? $schemaComponent->getKey(false) ?? $schemaComponent->getId())
        ) {
            $normalizedName = ConventionRegistry::sanitizeComponentPath($name);
        }

        if (
            $schemaComponent instanceof Schemas\Schema
            && ($parentComponent = $schemaComponent->getParentComponent())
        ) {
            if ($parentComponent instanceof Schemas\Components\Fieldset) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Schemas\Components\Section) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Schemas\Components\Tabs) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "tabs.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Schemas\Components\Tabs\Tab) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Schemas\Components\Wizard) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, $key, $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Schemas\Components\Wizard\Step) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Repeater) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Infolists\Components\RepeatableEntry) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if ($parentComponent instanceof Forms\Components\Builder\Block) {
                return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, "schema.{$key}", $replace, $number, allowNull: $allowNull);
            }

            if (
                $livewire instanceof Tables\Contracts\HasTable
                && ($schemaComponentKey = $schemaComponent->getKey())
                && Str::startsWith($schemaComponentKey, 'tableFiltersForm')
                && ($filter = $livewire->getTable()->getFilter(Str::after($schemaComponentKey, 'tableFiltersForm.')))
            ) {
                return ConventionRegistry::resolveTableLabel($filter, TableScope::Filters, $group ? "schema.{$group->value}.{$key}" : "schema.{$key}", $replace, $number, $allowNull);
            }

            return ConventionRegistry::resolveSchemaLabel($parentComponent, $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Actions) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer(), $group, $key, $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Tabs) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Tabs\Tab) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Wizard) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer(), $group, "{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Wizard\Step) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer()->getParentComponent(), $group, "steps.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Forms\Components\Builder\Block) {
            return ConventionRegistry::resolveSchemaLabel($schemaComponent->getContainer(), $group, "blocks.{$normalizedName}.{$key}", $replace, $number, allowNull: $allowNull);
        }

        if ($schemaComponent instanceof Schemas\Components\Group || $schemaComponent instanceof Schemas\Components\Grid) {
            if (
                $livewire instanceof Tables\Contracts\HasTable
                && ($schemaComponentKey = $schemaComponent->getKey())
                && ($filter = $livewire->getTable()->getFilter($schemaComponentKey))
            ) {
                return ConventionRegistry::resolveTableLabel($filter, TableScope::Filters, $group ? "form.{$group->value}.{$key}" : "form.{$key}", $replace, $number, $allowNull);
            }
        }

        /** @var Schemas\Schema $container */
        $container = $schemaComponent instanceof Schemas\Schema ? $schemaComponent : $schemaComponent->getContainer();

        if ($container !== $schemaComponent) {
            return ConventionRegistry::resolveSchemaLabel($container, $group, $normalizedName ? "{$normalizedName}.{$key}" : $key, $replace, $number, allowNull: $allowNull);
        }

        $lastOperation = $container->getOperation();

        if (Str::contains($lastOperation, '.')) {
            $lastOperation = Str::afterLast($lastOperation, '.');
        }

        $key = match (true) {
            $normalizedName && $group => "{$group->value}.{$normalizedName}.{$key}",
            $normalizedName && ! $group => "{$normalizedName}.{$key}",
            ! $normalizedName && $group => "{$group->value}.{$key}",
            ! $normalizedName && ! $group => $key,
        };

        $mountedAction = null;

        if ($livewire instanceof Actions\Contracts\HasActions) {
            // Note: do not use `getMountedAction()`, as that will cause an infinite loop
            // when a translation is requested *during* the caching of an action..

            foreach (invade($livewire)->cachedMountedActions ?? [] as $cachedMountedAction) {
                if ($cachedMountedAction->getName() === $lastOperation) {
                    /** @var Actions\Action $mountedAction */
                    $mountedAction = $cachedMountedAction;

                    break;
                }
            }
        }

        if ($mountedAction && in_array($mountedAction->getName(), ['createOption', 'editOption'])) {
            /** @var ?Forms\Components\Select $schemaComponent */
            $schemaComponent = $mountedAction->getSchemaComponent();

            if ($schemaComponent) {
                $createOptionActionForm = invade($schemaComponent)->createOptionActionForm;
                $editOptionActionForm = invade($schemaComponent)->editOptionActionForm;

                $namespace = match (true) {
                    $createOptionActionForm === $editOptionActionForm => 'manage_option_form',
                    $lastOperation === 'createOption' && $createOptionActionForm !== $editOptionActionForm => 'create_option_form',
                    $lastOperation === 'editOption' && $createOptionActionForm !== $editOptionActionForm => 'edit_option_form',
                };

                return ConventionRegistry::resolveSchemaLabel($schemaComponent, SchemaScope::Components, "{$namespace}.{$key}", $replace, $number, $allowNull);
            }
        }

        if (
            (
                ($livewire instanceof Resources\Pages\CreateRecord && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\EditRecord && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ListRecords && $lastOperation === 'view')
                || ($livewire instanceof Resources\Pages\ManageRecords && $lastOperation === 'view')
                || ($livewire instanceof Resources\Pages\ViewRecord && ($lastOperation === 'view' || (class_exists($lastOperation) && is_a($lastOperation, Resources\Pages\ViewRecord::class, true))))
            )
            && class_implements($resource = $livewire::getResource(), TranslatesConventionally::class)
        ) {
            $schemaComponentName = $schemaComponent->getName();

            if ($schemaComponentName === 'infolist') {
                return $resource::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Form, pageLabelContextKey: 'infolist');
            }

            if (
                Str::startsWith($schemaComponentName, 'mountedActionSchema')
                && $lastOperation === 'view'
                && $resource::infolist(Schemas\Schema::make())->getComponents()
            ) {
                return $resource::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Form, pageLabelContextKey: 'infolist');
            }

            /** @var class-string<TranslatesConventionally> $resource */
            return $resource::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Form);
        }

        if (
            (
                ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'create')
                || ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'edit')
                || ($livewire instanceof Resources\Pages\ManageRelatedRecords && $lastOperation === 'view')
            )
            && class_implements($livewire, TranslatesConventionally::class)
        ) {
            if (
                ! $mountedAction
                // If you are on a `ManageRelatedRecords` page, only the table actions will inherit the form automatically.
                // If the current action is not a table action, we will not inherit the main page form, but from the action.
                || ($mountedAction->getTable())
            ) {
                if (
                    $mountedAction
                    && ($conventionKey = $mountedAction->getConventionKey())
                    && $mountedAction->isConventionKeyAbsolute()
                ) {
                    return ConventionRegistry::lookupAbsoluteKey($conventionKey, "form.{$key}", $replace, $number, $allowNull);
                }

                /** @var class-string<TranslatesConventionally> $livewire */
                return $livewire::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Form);
            }
        }

        if (
            (
                ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'create')
                || ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'edit')
                || ($livewire instanceof Resources\RelationManagers\RelationManager && $lastOperation === 'view')
            )
            && class_implements($livewire, TranslatesConventionally::class)
        ) {
            // if (
            //    $mountedAction
            //    && ( $conventionKey = $mountedAction->getConventionKey() )
            //    && $mountedAction->isConventionKeyAbsolute()
            // ) {
            //    return ConventionRegistry::lookupAbsoluteKey($conventionKey, "form.{$key}", $replace, $number, $allowNull);
            // }

            /** @var class-string<TranslatesConventionally> $livewire */
            return $livewire::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Form);
        }

        if ($mountedAction) {
            return ConventionRegistry::resolveActionLabel($mountedAction, ActionScope::Schema, $key, $replace, $number, allowNull: $allowNull);
        }

        $schemaComponentName = $schemaComponent->getName();

        // We reached the bottom we can go (`$schemaComponent instanceof Schemas\Schema`)..
        return $livewire::resolveLabel(
            key: $key,
            replace: $replace,
            number: $number,
            allowNull: $allowNull,
            pageLabelContext: PageLabelContext::Form,
            pageLabelContextKey: Str::snake($schemaComponentName)
        );
    }

    public static function resolveTableLabel(Tables\Table|Tables\Columns\Column|Tables\Columns\ColumnGroup|Tables\Columns\Summarizers\Summarizer|Tables\Filters\BaseFilter|Tables\Grouping\Group $tableComponent, ?TableScope $group, string $key, array $replace = [], Countable|float|int|null $number = null, bool $allowNull = false): ?string
    {
        $livewire = $tableComponent->getLivewire();

        if (! $livewire instanceof TranslatesConventionally) {
            return null;
        }

        if (
            $tableComponent->isConventionKeyAbsolute()
            && ($conventionKey = $tableComponent->getConventionKey()) !== null
        ) {
            if ($group) {
                $key = "{$group->value}.{$key}";
            }

            return ConventionRegistry::lookupAbsoluteKey($conventionKey, $key, $replace, $number, $allowNull);
        }

        $normalizedName = match (true) {
            $tableComponent instanceof Tables\Columns\Column => ConventionRegistry::sanitizeComponentPath($tableComponent->getConventionKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Columns\ColumnGroup => ConventionRegistry::sanitizeComponentPath($tableComponent->getConventionKey() ?? $tableComponent->getConventionKey()),
            $tableComponent instanceof Tables\Columns\Summarizers\Summarizer => ConventionRegistry::sanitizeComponentPath($tableComponent->getConventionKey() ?? $tableComponent->getId() ?? str($tableComponent::class)->classBasename()->kebab()),
            $tableComponent instanceof Tables\Filters\BaseFilter => ConventionRegistry::sanitizeComponentPath($tableComponent->getConventionKey() ?? $tableComponent->getName()),
            $tableComponent instanceof Tables\Grouping\Group => ConventionRegistry::sanitizeComponentPath($tableComponent->getConventionKey() ?? $tableComponent->getId()),
            default => null,
        };

        if ($tableComponent instanceof Tables\Columns\Summarizers\Summarizer) {
            return ConventionRegistry::resolveTableLabel($tableComponent->getColumn(), TableScope::Columns, "summarizers.{$normalizedName}.{$key}", $replace, $number, $allowNull);
        }

        $key = match (true) {
            $normalizedName && $group => "{$group->value}.{$normalizedName}.{$key}",
            $normalizedName && ! $group => "{$normalizedName}.{$key}",
            ! $normalizedName && $group => "{$group->value}.{$key}",
            ! $normalizedName && ! $group => $key,
        };

        return $livewire::resolveLabel($key, $replace, $number, allowNull: $allowNull, pageLabelContext: PageLabelContext::Table);
    }
}
