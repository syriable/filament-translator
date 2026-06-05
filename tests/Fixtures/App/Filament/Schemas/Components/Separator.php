<?php

namespace TranslatorFixtures\App\Filament\Schemas\Components;

use Closure;
use Filament\Schemas\Components\Component;

/**
 * Minimal custom schema component (display copy via `text()`/`getText()`), used to exercise the
 * config-driven custom-component registration from issue #28.
 */
class Separator extends Component
{
    protected string | Closure | null $text = null;

    public static function make(string $name): static
    {
        $static = app(static::class);
        $static->key($name);
        $static->configure();

        return $static;
    }

    public function text(string | Closure | null $text): static
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->evaluate($this->text);
    }
}
