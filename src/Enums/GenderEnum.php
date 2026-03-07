<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasLabel;

enum GenderEnum: int implements HasLabel
{
    case Male = 1;
    case Female = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::Male => __('j-rh::j-rh.male'),
            self::Female => __('j-rh::j-rh.female'),
        };
    }
}
