<?php

namespace Syriable\Filament\Plugins\Translator\Enums;

enum TableScope: string
{
    case Actions = 'actions';
    case BulkActions = 'bulk_actions';
    case Columns = 'columns';
    case Filters = 'filters';
    case Groups = 'groups';
}
