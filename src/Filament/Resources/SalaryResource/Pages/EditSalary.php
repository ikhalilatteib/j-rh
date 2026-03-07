<?php

namespace Ikay\JRh\Filament\Resources\SalaryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Ikay\JRh\Filament\Resources\SalaryResource;

class EditSalary extends EditRecord
{
    protected static string $resource = SalaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['net_salary'] = ($data['base_salary'] ?? 0) + ($data['prime'] ?? 0) - ($data['advance_deductions'] ?? 0);

        return $data;
    }
}
