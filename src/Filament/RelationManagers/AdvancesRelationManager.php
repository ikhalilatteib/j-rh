<?php

namespace Ikay\JRh\Filament\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AdvancesRelationManager extends RelationManager
{
    protected static string $relationship = 'advances';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('j-rh::j-rh.advances');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $currency = config('j-rh.currency', 'XAF');

        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('date')
                    ->label(__('j-rh::j-rh.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label(__('j-rh::j-rh.amount'))
                    ->money($currency),
                TextColumn::make('reason')
                    ->label(__('j-rh::j-rh.reason'))
                    ->limit(30),
                TextColumn::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->badge(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
