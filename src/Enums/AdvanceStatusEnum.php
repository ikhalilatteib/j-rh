<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum AdvanceStatusEnum: int implements HasColor, HasIcon, HasLabel
{
    case Pending = 0;
    case Approved = 1;
    case Rejected = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('j-rh::j-rh.pending'),
            self::Approved => __('j-rh::j-rh.approved'),
            self::Rejected => __('j-rh::j-rh.rejected'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::Pending => 'heroicon-o-clock',
            self::Approved => 'heroicon-o-check-circle',
            self::Rejected => 'heroicon-o-x-circle',
        };
    }
}
