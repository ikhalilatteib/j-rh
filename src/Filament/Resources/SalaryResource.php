<?php

namespace Ikay\JRh\Filament\Resources;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\CreateSalary;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\EditSalary;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\ListSalaries;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\ViewSalary;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Illuminate\Database\Eloquent\Model;

class SalaryResource extends Resource
{
    protected static ?string $model = Salary::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return __('j-rh::j-rh.hr_management');
    }

    public static function getModelLabel(): string
    {
        return __('j-rh::j-rh.salary_payment');
    }

    public static function getPluralModelLabel(): string
    {
        return __('j-rh::j-rh.salary_payments');
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
                    ->preload()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $employee = Employee::find($state);
                            if ($employee) {
                                $set('base_salary', $employee->salary ?? 0);
                            }

                            $remaining = Advance::remainingOutstanding((int) $state);

                            $set('advance_deductions', $remaining);
                            $set('total_outstanding_advances', $remaining);
                        }
                    })
                    ->live(),

                Select::make('month')
                    ->label(__('j-rh::j-rh.month'))
                    ->options(collect(range(1, 12))->mapWithKeys(fn ($month) => [
                        $month => \Carbon\Carbon::create()->month($month)->translatedFormat('F'),
                    ]))
                    ->required(),

                TextInput::make('year')
                    ->label(__('j-rh::j-rh.year'))
                    ->numeric()
                    ->default(now()->year)
                    ->required()
                    ->minValue(2020)
                    ->maxValue(2030),

                TextInput::make('base_salary')
                    ->label(__('j-rh::j-rh.base_salary'))
                    ->numeric()
                    ->required()
                    ->suffix($currency)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::recalculateNet($set, $get)),

                TextInput::make('prime')
                    ->label(__('j-rh::j-rh.prime'))
                    ->numeric()
                    ->default(0)
                    ->suffix($currency)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::recalculateNet($set, $get)),

                TextInput::make('advance_deductions')
                    ->label(__('j-rh::j-rh.advance_deductions'))
                    ->numeric()
                    ->default(0)
                    ->suffix($currency)
                    ->helperText(__('j-rh::j-rh.advance_deductions_help'))
                    ->live(onBlur: true)
                    ->disabled(function (callable $get): bool {
                        $employeeId = $get('employee_id');
                        if (! $employeeId) {
                            return true;
                        }

                        return Advance::remainingOutstanding((int) $employeeId) <= 0;
                    })
                    ->dehydrated()
                    ->rules([
                        fn (callable $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get): void {
                            $baseSalary = (float) ($get('base_salary') ?? 0);
                            $prime = (float) ($get('prime') ?? 0);
                            $totalSalary = $baseSalary + $prime;

                            if ((float) $value > $totalSalary) {
                                $fail(__('j-rh::j-rh.advance_deductions_exceeds_salary'));
                            }

                            $employeeId = $get('employee_id');
                            if ($employeeId) {
                                $remaining = Advance::remainingOutstanding((int) $employeeId);

                                if ((float) $value > $remaining) {
                                    $fail(__('j-rh::j-rh.advance_deductions_exceeds_advances'));
                                }
                            }
                        },
                    ])
                    ->afterStateUpdated(fn ($state, callable $set, callable $get) => self::recalculateNet($set, $get)),

                TextInput::make('net_salary')
                    ->label(__('j-rh::j-rh.net_salary'))
                    ->numeric()
                    ->suffix($currency)
                    ->disabled()
                    ->dehydrated(),

                Placeholder::make('total_outstanding_advances')
                    ->label(__('j-rh::j-rh.total_outstanding_advances'))
                    ->content(function (callable $get) use ($currency): string {
                        $employeeId = $get('employee_id');
                        if (! $employeeId) {
                            return '0 '.$currency;
                        }

                        $remaining = Advance::remainingOutstanding((int) $employeeId);

                        return number_format($remaining, 0, ',', '.').' '.$currency;
                    }),

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
                TextColumn::make('month')
                    ->label(__('j-rh::j-rh.month'))
                    ->formatStateUsing(fn (int $state) => \Carbon\Carbon::create()->month($state)->translatedFormat('F'))
                    ->sortable(),
                TextColumn::make('year')
                    ->label(__('j-rh::j-rh.year'))
                    ->sortable(),
                TextColumn::make('base_salary')
                    ->label(__('j-rh::j-rh.base_salary'))
                    ->money($currency)
                    ->sortable(),
                TextColumn::make('prime')
                    ->label(__('j-rh::j-rh.prime'))
                    ->money($currency)
                    ->sortable(),
                TextColumn::make('advance_deductions')
                    ->label(__('j-rh::j-rh.advance_deductions'))
                    ->money($currency)
                    ->sortable(),
                TextColumn::make('net_salary')
                    ->label(__('j-rh::j-rh.net_salary'))
                    ->money($currency)
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label(__('j-rh::j-rh.paid_at'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('j-rh::j-rh.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->options(SalaryStatusEnum::class),
                SelectFilter::make('employee_id')
                    ->label(__('j-rh::j-rh.employee'))
                    ->relationship('employee', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    Action::make('mark_paid')
                        ->label(__('j-rh::j-rh.mark_paid'))
                        ->requiresConfirmation()
                        ->color('success')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn (Salary $record) => $record->update([
                            'status' => SalaryStatusEnum::Paid,
                            'paid_at' => now(),
                        ]))
                        ->visible(fn (): bool => auth()->user()->can('Pay:Salary')),

                    Action::make('cancel')
                        ->label(__('j-rh::j-rh.cancel'))
                        ->requiresConfirmation()
                        ->color('danger')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn (Salary $record) => $record->update([
                            'status' => SalaryStatusEnum::Cancelled,
                        ]))
                        ->visible(fn (): bool => auth()->user()->can('Cancel:Salary')),

                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                ])->visible(fn (Salary $record): bool => $record->status === SalaryStatusEnum::Pending),
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
            'index' => ListSalaries::route('/'),
            'create' => CreateSalary::route('/create'),
            'view' => ViewSalary::route('/{record}'),
            'edit' => EditSalary::route('/{record}/edit'),
        ];
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status === SalaryStatusEnum::Pending;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status === SalaryStatusEnum::Pending;
    }

    protected static function recalculateNet(callable $set, callable $get): void
    {
        $baseSalary = (float) ($get('base_salary') ?? 0);
        $prime = (float) ($get('prime') ?? 0);
        $advanceDeductions = (float) ($get('advance_deductions') ?? 0);

        $set('net_salary', $baseSalary + $prime - $advanceDeductions);
    }
}
