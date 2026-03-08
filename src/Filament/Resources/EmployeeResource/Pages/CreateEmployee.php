<?php

namespace Ikay\JRh\Filament\Resources\EmployeeResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Ikay\JRh\Filament\Resources\EmployeeResource;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();

        return $data;
    }
}
