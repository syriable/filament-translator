<?php

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Syriable\Filament\Plugins\Translator\ConventionRegistry;
use TranslatorFixtures\App\Filament\Pages\TranslatingPage;

/**
 * Issues #63 / #49 — nested modal action label resolution. Parent-action discovery walks a parent's
 * extra modal footer actions to namespace a child action's label, and must not evaluate uncached
 * modal-action closures (which may type-hint a `Model $record` that is null before mount).
 */
beforeEach(function () {
    app(ConventionRegistry::class)->registerDefaults();
    resetTranslatorCaches([TranslatingPage::class]);
});

it('resolves a nested extra-modal-footer action label via parent discovery', function () {
    $livewire = new TranslatingPage();

    $child = Action::make('confirm');
    $parent = Action::make('publish')
        ->livewire($livewire)
        // An uncached modal action with a typed closure must not be evaluated during resolution.
        ->registerModalActions([
            Action::make('danger')->action(fn (Model $record) => $record->delete()),
        ])
        ->extraModalFooterActions([$child]);

    $child->livewire($livewire);

    // Expose the parent as a cached page action so discovery can find it.
    invade($livewire)->cachedActions = ['publish' => $parent];

    // The first read surfaces the (missing, required) convention key; seed it and read again.
    $resolvedKey = $child->getLabel();

    expect($resolvedKey)->toContain('publish')
        ->and($resolvedKey)->toContain('extra_modal_footer_actions')
        ->and($resolvedKey)->toContain('confirm');

    app('translator')->addLines([$resolvedKey => 'Confirm'], 'en');
    resetTranslatorCaches([TranslatingPage::class]);

    expect($child->getLabel())->toBe('Confirm');
});

it('does not throw when discovering past actions that register typed-closure modal actions', function () {
    $livewire = new TranslatingPage();

    $other = Action::make('archive')
        ->livewire($livewire)
        ->registerModalActions([
            Action::make('purge')->action(fn (Model $record) => $record->forceDelete()),
        ]);

    $target = Action::make('standalone')->livewire($livewire);

    invade($livewire)->cachedActions = ['archive' => $other, 'standalone' => $target];

    expect(fn () => $target->getLabel())->not->toThrow(Throwable::class);
});
