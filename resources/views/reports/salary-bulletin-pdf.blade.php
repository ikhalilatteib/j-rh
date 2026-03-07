<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ __('j-rh::j-rh.salary_bulletin') }} - {{ $salary->employee->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 30px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .bulletin-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            margin: 20px 0;
            padding: 8px;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px 8px;
            font-size: 11px;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 35%;
            color: #555;
        }

        .info-table .value {
            width: 65%;
        }

        .separator {
            border-top: 1px solid #d1d5db;
            margin: 15px 0;
        }

        .separator-bold {
            border-top: 2px solid #333;
            margin: 15px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #333;
            padding-bottom: 4px;
            border-bottom: 1px solid #d1d5db;
        }

        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .salary-table th {
            background-color: #f3f4f6;
            font-size: 11px;
            font-weight: bold;
            text-align: left;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }

        .salary-table td {
            font-size: 11px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
        }

        .salary-table .text-right {
            text-align: right;
        }

        .salary-table .total-row {
            background-color: #f3f4f6;
            font-weight: bold;
        }

        .salary-table .net-row {
            background-color: #1f2937;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }

        .two-columns {
            width: 100%;
        }

        .two-columns td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        .two-columns td:first-child {
            padding-right: 15px;
        }

        .two-columns td:last-child {
            padding-left: 15px;
        }

        .signature-box {
            margin-top: 40px;
            width: 100%;
        }

        .signature-box td {
            width: 50%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }

        .signature-box .label {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 40px;
            display: block;
        }

        .signature-box .line {
            border-top: 1px solid #333;
            width: 70%;
            margin: 40px auto 5px;
        }

        .signature-box .name {
            font-size: 10px;
            color: #666;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #999;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 3px;
        }

        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
    {{-- Company Header --}}
    <div class="header">
        <h1>{{ $settings->name ?? config('app.name') }}</h1>
        @if ($settings->address)
            <p>{{ $settings->address }}</p>
        @endif
        @if ($settings->phone)
            <p>{{ __('j-rh::j-rh.phone') }}: {{ $settings->phone }}</p>
        @endif
        @if ($settings->tax_number)
            <p>{{ $settings->tax_number }}</p>
        @endif
    </div>

    {{-- Bulletin Title --}}
    <div class="bulletin-title">
        {{ __('j-rh::j-rh.salary_bulletin') }} - {{ \Carbon\Carbon::create()->month($salary->month)->translatedFormat('F') }}
        {{ $salary->year }}
    </div>

    {{-- Employee & Company Info --}}
    <table class="two-columns">
        <tr>
            <td>
                <div class="section-title">{{ __('j-rh::j-rh.employee_info') }}</div>
                <table class="info-table">
                    <tr>
                        <td class="label">{{ __('j-rh::j-rh.employee') }}:</td>
                        <td class="value">{{ $salary->employee->name }}</td>
                    </tr>
                    @if ($salary->employee->email)
                        <tr>
                            <td class="label">{{ __('j-rh::j-rh.email') }}:</td>
                            <td class="value">{{ $salary->employee->email }}</td>
                        </tr>
                    @endif
                    @if ($salary->employee->phone)
                        <tr>
                            <td class="label">{{ __('j-rh::j-rh.phone') }}:</td>
                            <td class="value">{{ $salary->employee->phone }}</td>
                        </tr>
                    @endif
                    @if ($salary->employee->position)
                        <tr>
                            <td class="label">{{ __('j-rh::j-rh.position') }}:</td>
                            <td class="value">{{ $salary->employee->position }}</td>
                        </tr>
                    @endif
                    @if ($salary->employee->employee_id)
                        <tr>
                            <td class="label">{{ __('j-rh::j-rh.employee_id') }}:</td>
                            <td class="value">{{ $salary->employee->employee_id }}</td>
                        </tr>
                    @endif
                </table>
            </td>
            <td>
                <div class="section-title">{{ __('j-rh::j-rh.payment_info') }}</div>
                <table class="info-table">
                    <tr>
                        <td class="label">{{ __('j-rh::j-rh.period') }}:</td>
                        <td class="value">
                            {{ \Carbon\Carbon::create()->month($salary->month)->translatedFormat('F') }}
                            {{ $salary->year }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label">{{ __('j-rh::j-rh.status') }}:</td>
                        <td class="value">
                            <span
                                class="status-badge {{ $salary->status === \Ikay\JRh\Enums\SalaryStatusEnum::Paid ? 'status-paid' : ($salary->status === \Ikay\JRh\Enums\SalaryStatusEnum::Pending ? 'status-pending' : 'status-cancelled') }}">
                                {{ $salary->status->getLabel() }}
                            </span>
                        </td>
                    </tr>
                    @if ($salary->paid_at)
                        <tr>
                            <td class="label">{{ __('j-rh::j-rh.paid_at') }}:</td>
                            <td class="value">{{ $salary->paid_at->format('d/m/Y') }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="separator"></div>

    {{-- Salary Breakdown --}}
    <div class="section-title">{{ __('j-rh::j-rh.salary_details') }}</div>

    <table class="salary-table">
        <thead>
            <tr>
                <th>{{ __('j-rh::j-rh.designation') }}</th>
                <th class="text-right" style="width: 35%;">{{ __('j-rh::j-rh.amount') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ __('j-rh::j-rh.base_salary') }}</td>
                <td class="text-right">{{ format_currency($salary->base_salary) }}</td>
            </tr>
            <tr>
                <td>{{ __('j-rh::j-rh.prime') }}</td>
                <td class="text-right">{{ format_currency($salary->prime) }}</td>
            </tr>
            <tr class="total-row">
                <td>{{ __('j-rh::j-rh.gross_salary') }}</td>
                <td class="text-right">{{ format_currency($salary->base_salary + $salary->prime) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($salary->advance_deductions > 0)
        <div class="section-title">{{ __('j-rh::j-rh.deductions') }}</div>

        <table class="salary-table">
            <thead>
                <tr>
                    <th>{{ __('j-rh::j-rh.designation') }}</th>
                    <th class="text-right" style="width: 35%;">{{ __('j-rh::j-rh.amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ __('j-rh::j-rh.advance_deductions') }}</td>
                    <td class="text-right">- {{ format_currency($salary->advance_deductions) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <div class="separator-bold"></div>

    {{-- Net Salary --}}
    <table class="salary-table">
        <tbody>
            <tr class="net-row">
                <td>{{ __('j-rh::j-rh.net_salary') }}</td>
                <td class="text-right" style="width: 35%;">{{ format_currency($salary->net_salary) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Notes --}}
    @if ($salary->notes)
        <div style="margin-top: 15px;">
            <div class="section-title">{{ __('j-rh::j-rh.notes') }}</div>
            <p style="font-size: 10px; color: #666;">{{ $salary->notes }}</p>
        </div>
    @endif

    {{-- Signatures --}}
    <table class="signature-box">
        <tr>
            <td>
                <span class="label">{{ __('j-rh::j-rh.employer_signature') }}</span>
                <div class="line"></div>
            </td>
            <td>
                <span class="label">{{ __('j-rh::j-rh.employee_signature') }}</span>
                <div class="line"></div>
                <span class="name">{{ $salary->employee->name }}</span>
            </td>
        </tr>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>{{ __('j-rh::j-rh.bulletin_generated_at') }}: {{ now()->format('d/m/Y H:i') }}</p>
        @if ($settings->website)
            <p>{{ $settings->website }}</p>
        @endif
    </div>
</body>

</html>
