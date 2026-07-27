<?php

/**
 * Filament contract shims for Filament-free installations.
 *
 * The J-RH enums (Ikay\JRh\Enums\*) implement Filament's HasLabel / HasColor / HasIcon
 * contracts so that Filament renders translated labels, badge colours and icons for them.
 * Those contracts live in filament/support, which transitively pulls in Livewire.
 *
 * Non-Filament consumers only want the Eloquent core, so filament/filament is no longer a
 * hard requirement of this package. When it is absent the enums would fatal at class-load
 * time ("Interface Filament\Support\Contracts\HasLabel not found"), which would in turn
 * break every model that casts to them.
 *
 * This file declares minimal, behaviour-free stand-ins for exactly those three interfaces,
 * and ONLY when Filament is not installed. When filament/support is present the real
 * interfaces win (interface_exists() resolves them through Composer's autoloader first) and
 * nothing here is loaded, so Filament consumers see byte-for-byte the previous behaviour.
 *
 * The stub signatures are deliberately permissive so that both Filament v4 and v5 concrete
 * implementations remain covariant-compatible with them.
 */
if (! interface_exists(\Filament\Support\Contracts\HasLabel::class)) {
    require_once __DIR__.'/stubs/Filament/Support/Contracts/HasLabel.php';
}

if (! interface_exists(\Filament\Support\Contracts\HasColor::class)) {
    require_once __DIR__.'/stubs/Filament/Support/Contracts/HasColor.php';
}

if (! interface_exists(\Filament\Support\Contracts\HasIcon::class)) {
    require_once __DIR__.'/stubs/Filament/Support/Contracts/HasIcon.php';
}
