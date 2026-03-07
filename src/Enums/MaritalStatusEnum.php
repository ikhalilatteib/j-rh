<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasLabel;

enum MaritalStatusEnum: int implements HasLabel
{
    case Single = 1;
    case Married = 2;
    case Divorced = 3;
    case Widowed = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => __('j-rh::j-rh.single'),
            self::Married => __('j-rh::j-rh.married'),
            self::Divorced => __('j-rh::j-rh.divorced'),
            self::Widowed => __('j-rh::j-rh.widowed'),
        };
    }
}
