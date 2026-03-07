<?php

namespace Ikay\JRh;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Ikay\JRh\Filament\Resources\AdvanceResource;
use Ikay\JRh\Filament\Resources\EmployeeResource;
use Ikay\JRh\Filament\Resources\SalaryResource;

class JRhPlugin implements Plugin
{
    public static function make(): static
    {
        return new static;
    }

    public function getId(): string
    {
        return 'j-rh';
    }

    public function register(Panel $panel): void
    {
        $panel->resources([
            EmployeeResource::class,
            SalaryResource::class,
            AdvanceResource::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
