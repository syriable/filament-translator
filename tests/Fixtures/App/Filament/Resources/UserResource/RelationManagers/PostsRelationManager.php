<?php

namespace TranslatorFixtures\App\Filament\Resources\UserResource\RelationManagers;

use Syriable\Filament\Plugins\Translator\Concerns\ResolvesRelationManagerLabels;

/**
 * Fixture relation manager living under a realistic
 * `App\Filament\Resources\UserResource\RelationManagers\...` namespace so that
 * {@see ResolvesRelationManagerLabels::resolveResourceClass()} resolves back to the sibling
 * `UserResource` fixture and labels nest under `relation_managers.posts_relation_manager.*`.
 */
class PostsRelationManager
{
    use ResolvesRelationManagerLabels;
}
