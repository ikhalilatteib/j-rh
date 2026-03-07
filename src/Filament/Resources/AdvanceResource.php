<?php

namespace Ikay\JRh\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\CreateAdvance;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\EditAdvance;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\ListAdvances;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\ViewAdvance;
use Ikay\JRh\Models\Advance;
use Illuminate\Database\Eloquent\Model;

class AdvanceResource extends Resource
{
    protected static ?string $model = Advance::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('j-rh::j-rh.hr_management');
    }

    public static function getModelLabel(): string
    {
        return __('j-rh::j-rh.advance');
    }

    public static function getPluralModelLabel(): string
    {
        return __('j-rh::j-rh.advances');
    }

    public static function form(Schema $schema): Schema
    {
        $currency = config('j-rh.currency', 'XAF');

        return $schema
            ->components([
                Select::make('employee_id')
                    ->label(__('j-rh::j-rh.employee'))
                    ->relationship('employee', 'name')
                    ->required()
                    ->searchable()
                    ->preload(),

                TextInput::make('amount')
                    ->label(__('j-rh::j-rh.amount'))
                    ->numeric()
                    ->required()
                    ->suffix($currency),

                DatePicker::make('date')
                    ->label(__('j-rh::j-rh.date'))
                    ->default(now())
                    ->required(),

                TextInput::make('reason')
                    ->label(__('j-rh::j-rh.reason'))
                    ->maxLength(255),

                Textarea::make('notes')
                    ->label(__('j-rh::j-rh.notes'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        $currency = config('j-rh.currency', 'XAF');

        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label(__('j-rh::j-rh.employee'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('amount')
                    ->label(__('j-rh::j-rh.amount'))
                    ->money($currency)
                    ->sortable(),
                TextColumn::make('date')
                    ->label(__('j-rh::j-rh.date'))
                    ->date()
                    ->sortable(),
                TextColumn::make('reason')
                    ->label(__('j-rh::j-rh.reason'))
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('j-rh::j-rh.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->options(AdvanceStatusEnum::class),
                SelectFilter::make('employee_id')
                    ->label(__('j-rh::j-rh.employee'))
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label(__('j-rh::j-rh.approve'))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Advance $record) => $record->update([
                            'status' => AdvanceStatusEnum::Approved,
                        ]))
                        ->visible(fn (): bool => auth()->user()->can('Approve:Advance')),

                    Action::make('reject')
                        ->label(__('j-rh::j-rh.reject'))
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Advance $record) => $record->update([
                            'status' => AdvanceStatusEnum::Rejected,
                        ]))
                        ->visible(fn (): bool => auth()->user()->can('Reject:Advance')),

                    ViewAction::make(),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                ])->visible(fn (Advance $record): bool => $record->status === AdvanceStatusEnum::Pending),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvances::route('/'),
            'create' => CreateAdvance::route('/create'),
            'view' => ViewAdvance::route('/{record}'),
            'edit' => EditAdvance::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status === AdvanceStatusEnum::Pending;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status === AdvanceStatusEnum::Pending;
    }
}
