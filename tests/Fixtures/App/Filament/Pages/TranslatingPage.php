<?php

namespace TranslatorFixtures\App\Filament\Pages;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Livewire\Component;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesPageLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

/**
 * A translating Livewire owner used to drive the ConventionRegistry resolver engine at the
 * harness level: real Filament schemas/tables/actions are attached to it and their labels are
 * resolved through the registered `configureUsing` closures without a full page render.
 *
 * Lives under a realistic `App\Filament\Pages\...` namespace so the derived convention namespace
 * is `filament/pages/translating-page`.
 */
class TranslatingPage extends Component implements HasActions, HasSchemas, HasTable, TranslatesConventionally
{
    use InteractsWithActions;
    use InteractsWithSchemas;
    use InteractsWithTable;
    use ResolvesPageLabels;

    public function table(Table $table): Table
    {
        return $table;
    }

    public function render(): string
    {
        return '<div></div>';
    }

    public static function exposedConventionNamespace(): string
    {
        return static::resolveConventionNamespace();
    }
}
