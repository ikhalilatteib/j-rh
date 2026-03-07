<?php

namespace Ikay\JRh\Filament\RelationManagers;

use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Illuminate\Database\Eloquent\Model;

class SalariesRelationManager extends RelationManager
{
    protected static string $relationship = 'salaries';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('j-rh::j-rh.salary_payments');
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
                TextColumn::make('month')
                    ->label(__('j-rh::j-rh.month'))
                    ->formatStateUsing(fn (int $state) => \Carbon\Carbon::create()->month($state)->translatedFormat('F'))
                    ->sortable(),
                TextColumn::make('year')
                    ->label(__('j-rh::j-rh.year'))
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label(__('j-rh::j-rh.base_salary'))
                    ->money($currency),
                TextColumn::make('prime')
                    ->label(__('j-rh::j-rh.prime'))
                    ->money($currency),
                TextColumn::make('advance_deductions')
                    ->label(__('j-rh::j-rh.advance_deductions'))
                    ->money($currency),
                TextColumn::make('net_salary')
                    ->label(__('j-rh::j-rh.net_salary'))
                    ->money($currency)
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->badge(),
            ])
            ->filters([])
            ->headerActions([])
            ->recordActions([
                Action::make('mark_paid')
                    ->label(__('j-rh::j-rh.mark_paid'))
                    ->requiresConfirmation()
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->action(fn (Model $record) => $record->update([
                        'status' => SalaryStatusEnum::Paid,
                        'paid_at' => now(),
                    ]))
                    ->visible(fn (Model $record): bool => $record->status === SalaryStatusEnum::Pending),
            ])
            ->toolbarActions([]);
    }
}
