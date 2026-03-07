<?php

namespace Ikay\JRh\Filament\Resources\AdvanceResource\Pages;

use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ikay\JRh\Filament\Resources\AdvanceResource;

class ViewAdvance extends ViewRecord
{
    protected static string $resource = AdvanceResource::class;

    public function infolist(Schema $schema): Schema
    {
        $currency = config('j-rh.currency', 'XAF');

        return $schema
            ->columns(1)
            ->schema([
                Section::make(__('j-rh::j-rh.advance_information'))
                    ->schema([
                        TextEntry::make('employee.name')
                            ->label(__('j-rh::j-rh.employee')),
                        TextEntry::make('amount')
                            ->label(__('j-rh::j-rh.amount'))
                            ->money($currency),
                        TextEntry::make('date')
                            ->label(__('j-rh::j-rh.date'))
                            ->date(),
                        TextEntry::make('status')
                            ->label(__('j-rh::j-rh.status'))
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make(__('j-rh::j-rh.additional_information'))
                    ->schema([
                        TextEntry::make('reason')
                            ->label(__('j-rh::j-rh.reason'))
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label(__('j-rh::j-rh.notes'))
                            ->columnSpanFull()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label(__('j-rh::j-rh.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('j-rh::j-rh.updated_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }
}
