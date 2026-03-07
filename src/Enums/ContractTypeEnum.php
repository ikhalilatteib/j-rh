<?php

namespace Ikay\JRh\Enums;

use Filament\Support\Contracts\HasLabel;

enum ContractTypeEnum: int implements HasLabel
{
    case Permanent = 1;
    case Temporary = 2;
    case Freelance = 3;
    case Intern = 4;

    public function getLabel(): string
    {
        return match ($this) {
            self::Permanent => __('j-rh::j-rh.permanent'),
            self::Temporary => __('j-rh::j-rh.temporary'),
            self::Freelance => __('j-rh::j-rh.freelance'),
            self::Intern => __('j-rh::j-rh.intern'),
        };
    }
}
