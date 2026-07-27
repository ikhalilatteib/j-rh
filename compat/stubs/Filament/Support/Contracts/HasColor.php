<?php

namespace Filament\Support\Contracts;

/**
 * Stand-in for filament/support's HasColor contract.
 *
 * Only loaded by compat/filament-contracts.php when filament/support is NOT installed.
 * Do not reference this file directly.
 */
interface HasColor
{
    /**
     * @return string|array<int|string, string|int>|null
     */
    public function getColor(): string|array|null;
}
