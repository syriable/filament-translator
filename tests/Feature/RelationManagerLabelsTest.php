<?php

use Syriable\Filament\Plugins\Translator\Enums\PageLabelContext;
use TranslatorFixtures\App\Filament\Resources\UserResource;
use TranslatorFixtures\App\Filament\Resources\UserResource\RelationManagers\PostsRelationManager;

/**
 * Issue #59 — Relation manager label resolution. Keys nest under the owning resource namespace as
 * `relation_managers.{relation_manager}.{context}.{key}`.
 */
beforeEach(function () {
    resetTranslatorCaches([UserResource::class, PostsRelationManager::class]);
});

it('resolves a relation manager title under the resource namespace', function () {
    app('translator')->addLines([
        UserResource::exposedConventionNamespace() . '.relation_managers.posts_relation_manager.title' => 'Posts',
    ], 'en');

    expect(PostsRelationManager::resolveLabel('title'))->toBe('Posts');
});

it('nests a form-context relation manager label under the form segment', function () {
    app('translator')->addLines([
        UserResource::exposedConventionNamespace() . '.relation_managers.posts_relation_manager.form.title.label' => 'Post title',
    ], 'en');

    expect(PostsRelationManager::resolveLabel('title.label', pageLabelContext: PageLabelContext::Form))
        ->toBe('Post title');
});

it('returns null for a missing null-allowed relation manager label', function () {
    expect(PostsRelationManager::resolveLabel('title', allowNull: true))->toBeNull();
});
