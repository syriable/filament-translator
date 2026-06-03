<?php

namespace Syriable\Filament\Plugins\Translator\Filament\Clusters;

use Filament\Clusters\Cluster as BaseCluster;
use Syriable\Filament\Plugins\Translator\Concerns\ResolvesClusterLabels;
use Syriable\Filament\Plugins\Translator\Contracts\TranslatesConventionally;

abstract class TranslatableCluster extends BaseCluster implements TranslatesConventionally
{
    use ResolvesClusterLabels;
}
