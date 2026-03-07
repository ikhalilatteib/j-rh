<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum SalaryStatusEnum: int implements HasColor, HasIcon, HasLabel
{
    case Pending = 0;
    case Paid = 1;
    case Cancelled = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('j-rh::j-rh.pending'),
            self::Paid => __('j-rh::j-rh.paid'),
            self::Cancelled => __('j-rh::j-rh.cancelled'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Paid => 'heroicon-o-check-circle',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
