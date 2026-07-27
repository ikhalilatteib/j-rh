<?php

namespace Filament\Support\Contracts;

/**
 * Stand-in for filament/support's HasLabel contract.
 *
 * Only loaded by compat/filament-contracts.php when filament/support is NOT installed.
 * Do not reference this file directly.
 */
interface HasLabel
{
    public function getLabel(): ?string;
}
