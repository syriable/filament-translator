<?php

namespace Syriable\Filament\Plugins\Translator\Enums;

enum ResolutionMode: string
{
    case Strict = 'strict';
    case Balanced = 'balanced';
    case Loose = 'loose';
}
