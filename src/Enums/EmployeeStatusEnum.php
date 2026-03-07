<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EmployeeStatusEnum: int implements HasColor, HasIcon, HasLabel
{
    case Active = 1;
    case Inactive = 2;
    case Suspended = 3;
    case OnLeave = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Active => __('j-rh::j-rh.active'),
            self::Inactive => __('j-rh::j-rh.inactive'),
            self::Suspended => __('j-rh::j-rh.suspended'),
            self::OnLeave => __('j-rh::j-rh.on_leave'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'warning',
            self::Suspended => 'danger',
            self::OnLeave => 'secondary',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Active => 'heroicon-o-check-circle',
            self::Inactive => 'heroicon-o-ban',
            self::Suspended => 'heroicon-o-exclamation-triangle',
            self::OnLeave => 'heroicon-o-clock',
        };
    }
}
