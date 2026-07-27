<?php

namespace Filament\Support\Contracts;

/**
 * Stand-in for filament/support's HasIcon contract.
 *
 * Only loaded by compat/filament-contracts.php when filament/support is NOT installed.
 * Do not reference this file directly.
 *
 * The return type is intentionally `mixed`: Filament v4 declares
 * `string|BackedEnum|Htmlable|null` while v3 declared `?string`, and a permissive stub keeps
 * every concrete implementation covariant-compatible regardless of which one it was written
 * against.
 */
interface HasIcon
{
    public function getIcon(): mixed;
}
