<?php

namespace Syriable\Filament\Plugins\Translator\Plugin;

/**
 * Allows each panel plugin instance to declare namespace → lang path aliases.
 */
trait ConfiguresPathAliases
{
    /**
     * @var array<string, string>
     */
    protected array $pathAliases = [];

    public function pathAliases(array $pathAliases, bool $merge = true): static
    {
        if ($merge) {
            $this->pathAliases = [...$this->pathAliases, ...$pathAliases];
        } else {
            $this->pathAliases = $pathAliases;
        }

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPathAliases(): array
    {
        return $this->pathAliases;
    }
}
