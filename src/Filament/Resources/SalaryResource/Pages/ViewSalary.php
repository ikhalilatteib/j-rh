<?php

namespace Ikay\JRh\Filament\Resources\SalaryResource\Pages;

use App\Settings\AppSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Filament\Resources\SalaryResource;
use Ikay\JRh\Models\Salary;

class ViewSalary extends ViewRecord
{
    protected static string $resource = SalaryResource::class;

    public function infolist(Schema $schema): Schema
    {
        $currency = config('j-rh.currency', 'XAF');

        return $schema
            ->columns(1)
            ->schema([
                Section::make(__('j-rh::j-rh.salary_information'))
                    ->schema([
                        TextEntry::make('employee.name')
                            ->label(__('j-rh::j-rh.employee')),
                        TextEntry::make('month')
                            ->label(__('j-rh::j-rh.month'))
                            ->formatStateUsing(fn (int $state) => \Carbon\Carbon::create()->month($state)->translatedFormat('F')),
                        TextEntry::make('year')
                            ->label(__('j-rh::j-rh.year')),
                        TextEntry::make('status')
                            ->label(__('j-rh::j-rh.status'))
                            ->badge(),
                    ])
                    ->columns(2),

                Section::make(__('j-rh::j-rh.salary_details'))
                    ->schema([
                        TextEntry::make('base_salary')
                            ->label(__('j-rh::j-rh.base_salary'))
                            ->money($currency),
                        TextEntry::make('prime')
                            ->label(__('j-rh::j-rh.prime'))
                            ->money($currency),
                        TextEntry::make('advance_deductions')
                            ->label(__('j-rh::j-rh.advance_deductions'))
                            ->money($currency),
                        TextEntry::make('net_salary')
                            ->label(__('j-rh::j-rh.net_salary'))
                            ->money($currency)
                            ->weight('bold'),
                    ])
                    ->columns(2),

                Section::make(__('j-rh::j-rh.additional_information'))
                    ->schema([
                        TextEntry::make('paid_at')
                            ->label(__('j-rh::j-rh.paid_at'))
                            ->date()
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mark_paid')
                ->label(__('j-rh::j-rh.mark_paid'))
                ->requiresConfirmation()
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->action(fn (Salary $record) => $record->update([
                    'status' => SalaryStatusEnum::Paid,
                    'paid_at' => now(),
                ]))
                ->visible(fn (Salary $record): bool => $record->status === SalaryStatusEnum::Pending && auth()->user()->can('Pay:Salary')),

            Action::make('cancel')
                ->label(__('j-rh::j-rh.cancel'))
                ->requiresConfirmation()
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->action(fn (Salary $record) => $record->update([
                    'status' => SalaryStatusEnum::Cancelled,
                ]))
                ->visible(fn (Salary $record): bool => $record->status === SalaryStatusEnum::Pending && auth()->user()->can('Cancel:Salary')),

            Action::make('downloadBulletin')
                ->label(__('j-rh::j-rh.download_bulletin'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(fn () => $this->downloadBulletin()),
        ];
    }

    public function downloadBulletin(): mixed
    {
        $salary = $this->getRecord()->load('employee');
        $settings = app(AppSetting::class);

        $pdf = Pdf::loadView('j-rh::reports.salary-bulletin-pdf', [
            'salary' => $salary,
            'settings' => $settings,
        ]);

        $pdf->setPaper('a4');
        $pdf->setOption('defaultFont', 'DejaVu Sans');

        $monthName = \Carbon\Carbon::create()->month($salary->month)->format('F');
        $filename = 'bulletin-'.$salary->employee->name.'-'.$monthName.'-'.$salary->year.'.pdf';

        return response()->streamDownload(
            fn () => print ($pdf->output()),
            $filename,
            ['Content-Type' => 'application/pdf']
        );
    }
}
