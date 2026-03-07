<?php

namespace Ikay\JRh\Filament\Resources\SalaryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Ikay\JRh\Filament\Resources\SalaryResource;

class CreateSalary extends CreateRecord
{
    protected static string $resource = SalaryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['net_salary'] = ($data['base_salary'] ?? 0) + ($data['prime'] ?? 0) - ($data['advance_deductions'] ?? 0);

        return $data;
    }
}
