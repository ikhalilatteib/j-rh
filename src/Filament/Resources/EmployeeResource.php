<?php

namespace Ikay\JRh\Filament\Resources;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Ikay\JRh\Enums\ContractTypeEnum;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Enums\GenderEnum;
use Ikay\JRh\Enums\MaritalStatusEnum;
use Ikay\JRh\Filament\RelationManagers\AdvancesRelationManager;
use Ikay\JRh\Filament\RelationManagers\SalariesRelationManager;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\ViewEmployee;
use Ikay\JRh\Models\Employee;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return __('j-rh::j-rh.hr_management');
    }

    public static function getModelLabel(): string
    {
        return __('j-rh::j-rh.employee');
    }

    public static function getPluralModelLabel(): string
    {
        return __('j-rh::j-rh.employees');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make(__('j-rh::j-rh.personal_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('j-rh::j-rh.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('j-rh::j-rh.email'))
                            ->email()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('j-rh::j-rh.phone'))
                            ->tel()
                            ->maxLength(255),
                        DatePicker::make('date_of_birth')
                            ->label(__('j-rh::j-rh.date_of_birth')),
                        Select::make('gender')
                            ->label(__('j-rh::j-rh.gender'))
                            ->options(GenderEnum::class),
                        Select::make('marital_status')
                            ->label(__('j-rh::j-rh.marital_status'))
                            ->options(MaritalStatusEnum::class),
                        TextInput::make('nationality')
                            ->label(__('j-rh::j-rh.nationality'))
                            ->maxLength(255),
                        TextInput::make('national_id')
                            ->label(__('j-rh::j-rh.national_id'))
                            ->maxLength(255),
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->label(__('j-rh::j-rh.photo'))
                            ->collection('photo')
                            ->image()
                            ->avatar()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('j-rh::j-rh.employment_information'))
                    ->schema([
                        TextInput::make('employee_id')
                            ->label(__('j-rh::j-rh.employee_id'))
                            ->disabled()
                            ->dehydrated(false)
                            ->visibleOn('edit'),
                        TextInput::make('position')
                            ->label(__('j-rh::j-rh.position'))
                            ->maxLength(255),
                        TextInput::make('department')
                            ->label(__('j-rh::j-rh.department'))
                            ->maxLength(255),
                        DatePicker::make('hired_at')
                            ->label(__('j-rh::j-rh.hired_at'))
                            ->required()
                            ->default(now()),
                        TextInput::make('salary')
                            ->label(__('j-rh::j-rh.salary'))
                            ->numeric()
                            ->suffix(config('j-rh.currency', 'XAF')),
                        Select::make('contract_type')
                            ->label(__('j-rh::j-rh.contract_type'))
                            ->options(ContractTypeEnum::class),
                        DatePicker::make('contract_end_date')
                            ->label(__('j-rh::j-rh.contract_end_date')),
                        Select::make('status')
                            ->label(__('j-rh::j-rh.status'))
                            ->options(EmployeeStatusEnum::class)
                            ->required()
                            ->default(EmployeeStatusEnum::Active),
                    ])
                    ->columns(2),

                Section::make(__('j-rh::j-rh.contact_banking'))
                    ->schema([
                        Textarea::make('address')
                            ->label(__('j-rh::j-rh.address'))
                            ->columnSpanFull(),
                        TextInput::make('emergency_contact')
                            ->label(__('j-rh::j-rh.emergency_contact'))
                            ->maxLength(255),
                        TextInput::make('bank_account')
                            ->label(__('j-rh::j-rh.bank_account'))
                            ->maxLength(255),
                    ])
                    ->columns(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_id')
                    ->label(__('j-rh::j-rh.employee_id'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('j-rh::j-rh.name'))
                    ->sortable()
                    ->searchable(),
                TextColumn::make('position')
                    ->label(__('j-rh::j-rh.position'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department')
                    ->label(__('j-rh::j-rh.department'))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label(__('j-rh::j-rh.phone'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label(__('j-rh::j-rh.status'))
                    ->badge()
                    ->sortable(),
                TextColumn::make('hired_at')
                    ->label(__('j-rh::j-rh.hired_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('salary')
                    ->label(__('j-rh::j-rh.salary'))
                    ->money(config('j-rh.currency', 'XAF'))
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
                    ->options(EmployeeStatusEnum::class),
                SelectFilter::make('department')
                    ->label(__('j-rh::j-rh.department'))
                    ->options(fn () => Employee::query()->distinct()->pluck('department', 'department')->filter()->toArray()),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->color('warning'),
                    DeleteAction::make(),
                ]),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            SalariesRelationManager::class,
            AdvancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'view' => ViewEmployee::route('/{record}'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
