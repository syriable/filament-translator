<?php

namespace Syriable\Filament\Plugins\Translator\Enums;

enum PageLabelContext: string
{
    case Actions = 'actions';
    case Schemas = 'schemas';
    case Form = 'form';
    case Infolist = 'infolist';
    case Table = 'table';
}
