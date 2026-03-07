<?php

namespace Ikay\JRh\Filament\Resources\AdvanceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Ikay\JRh\Filament\Resources\AdvanceResource;

class EditAdvance extends EditRecord
{
    protected static string $resource = AdvanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
