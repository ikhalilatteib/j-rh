<?php

namespace Ikay\JRh\Filament\Resources\EmployeeResource\Pages;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ikay\JRh\Filament\Resources\EmployeeResource;

class ViewEmployee extends ViewRecord
{
    protected static string $resource = EmployeeResource::class;

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make(__('j-rh::j-rh.personal_information'))
                    ->schema([
                        TextEntry::make('employee_id')
                            ->label(__('j-rh::j-rh.employee_id')),
                        TextEntry::make('name')
                            ->label(__('j-rh::j-rh.name')),
                        TextEntry::make('email')
                            ->label(__('j-rh::j-rh.email'))
                            ->placeholder('-'),
                        TextEntry::make('phone')
                            ->label(__('j-rh::j-rh.phone'))
                            ->placeholder('-'),
                        TextEntry::make('date_of_birth')
                            ->label(__('j-rh::j-rh.date_of_birth'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('gender')
                            ->label(__('j-rh::j-rh.gender'))
                            ->placeholder('-'),
                        TextEntry::make('nationality')
                            ->label(__('j-rh::j-rh.nationality'))
                            ->placeholder('-'),
                        TextEntry::make('national_id')
                            ->label(__('j-rh::j-rh.national_id'))
                            ->placeholder('-'),
                        TextEntry::make('marital_status')
                            ->label(__('j-rh::j-rh.marital_status'))
                            ->placeholder('-'),
                    ])
                    ->columns(3),

                Section::make(__('j-rh::j-rh.employment_information'))
                    ->schema([
                        TextEntry::make('position')
                            ->label(__('j-rh::j-rh.position'))
                            ->placeholder('-'),
                        TextEntry::make('department')
                            ->label(__('j-rh::j-rh.department'))
                            ->placeholder('-'),
                        TextEntry::make('hired_at')
                            ->label(__('j-rh::j-rh.hired_at'))
                            ->date(),
                        TextEntry::make('salary')
                            ->label(__('j-rh::j-rh.salary'))
                            ->money(config('j-rh.currency', 'XAF')),
                        TextEntry::make('contract_type')
                            ->label(__('j-rh::j-rh.contract_type'))
                            ->placeholder('-'),
                        TextEntry::make('contract_end_date')
                            ->label(__('j-rh::j-rh.contract_end_date'))
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label(__('j-rh::j-rh.status'))
                            ->badge(),
                    ])
                    ->columns(3),

                Section::make(__('j-rh::j-rh.contact_banking'))
                    ->schema([
                        TextEntry::make('address')
                            ->label(__('j-rh::j-rh.address'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('emergency_contact')
                            ->label(__('j-rh::j-rh.emergency_contact'))
                            ->placeholder('-'),
                        TextEntry::make('bank_account')
                            ->label(__('j-rh::j-rh.bank_account'))
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
