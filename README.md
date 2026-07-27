# J-RH - Employee HR Module for Laravel

A reusable Laravel package for managing employees, salaries, and advances. Designed to replace direct User-based salary/advance tracking with a dedicated Employee model.

The package is split into two layers:

| Layer | Contents | Needs Filament? |
|-------|----------|-----------------|
| **Core** | `src/Models`, `src/Enums`, `src/Policies`, `src/Traits`, migrations, factories, config, translations, views | No |
| **Admin layer** | `src/Filament/**`, `src/JRhPlugin.php` | Yes |

`filament/filament` and `barryvdh/laravel-dompdf` are **suggested**, not required. An application without a Filament panel can install this package for the Eloquent core alone; an application with a panel keeps registering `JRhPlugin` exactly as before.

## Features

- **Employee Management** - Full CRUD with auto-generated IDs (EMP-0001), extended HR fields (position, department, contract type, etc.)
- **Salary Management** - Monthly salary processing with base salary, primes, advance deductions, and net salary calculation
- **Advance Management** - Employee advance requests with approval workflow and automatic outstanding balance tracking
- **PDF Salary Bulletins** - Generate downloadable salary bulletins via DomPDF *(admin layer)*
- **Filament Shield Compatible** - Permissions auto-register with `BezhanSalleh/FilamentShield` *(admin layer)*
- **Multi-language** - French and Arabic translations included
- **User Linking** - Optionally link employees to app users for authentication

## Requirements

### Core

- PHP 8.2+
- Laravel 12+ (`illuminate/support`, `illuminate/database`)
- `spatie/laravel-medialibrary` ^11.0 - the `Employee` model implements `HasMedia` for its photo

### Admin layer (optional)

- `filament/filament` ^4.0 or ^5.0
- `barryvdh/laravel-dompdf` ^3.1 - salary bulletin PDFs

Install them in the consuming application; this package no longer pulls them in.

## Schema owned by the host application

The package creates the `employees` table only. It expects the application to already own:

- `users` - the employees migration adds a nullable `user_id` FK to it
- `salaries` and `advances` - migrations `000002`/`000003` only *re-key* existing tables from `user_id` to `employee_id`, and no-op when there is nothing to convert

A brand-new consumer therefore has to create `salaries` and `advances` itself. See `tests/TestCase.php` for the expected columns.

## Installation

### As a Composer Package

```bash
composer require ikay/j-rh
```

Using the Filament admin layer as well:

```bash
composer require ikay/j-rh filament/filament barryvdh/laravel-dompdf
```

### As a Local Package

Add the path repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "packages/ikay/j-rh"
        }
    ]
}
```

Then require it:

```bash
composer require ikay/j-rh:@dev
```

### Run Migrations

```bash
php artisan migrate
```

## Setup

### 1. Register the Filament Plugin *(admin layer only)*

Skip this step entirely if the application has no Filament panel; the models, enums and policies are registered by `JRhServiceProvider` on their own.

In your `AdminPanelProvider.php`:

```php
use Ikay\JRh\JRhPlugin;

->plugins([
    JRhPlugin::make(),
])
```

### 2. Add Navigation Group (optional)

```php
use Filament\Navigation\NavigationGroup;

->navigationGroups([
    NavigationGroup::make(fn () => __('j-rh::j-rh.hr_management')),
])
```

### 3. Add the HasEmployees Trait to Your User Model

```php
use Ikay\JRh\Traits\HasEmployees;

class User extends Authenticatable
{
    use HasEmployees;
}
```

### 4. Regenerate Shield Permissions

```bash
php artisan shield:generate --all
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=j-rh-config
```

```php
// config/j-rh.php
return [
    'user_model' => \App\Models\User::class,
    'employee_id_prefix' => 'EMP',
    'navigation_group' => 'j-rh::j-rh.hr_management',
    'currency' => 'XAF',
];
```

## Publishing Assets

```bash
# Migrations
php artisan vendor:publish --tag=j-rh-migrations

# Translations
php artisan vendor:publish --tag=j-rh-translations

# Views (salary bulletin template)
php artisan vendor:publish --tag=j-rh-views
```

## Data Migration

If you are migrating from a User-based salary/advance system, the package includes migrations that:

1. Create the `employees` table
2. Auto-create Employee records from existing User references in `salaries`
3. Auto-create Employee records from existing User references in `advances`
4. Re-link `salaries.user_id` and `advances.user_id` to `employees.employee_id`

These migrations are safe to run on fresh databases (they skip if `user_id` columns don't exist).

## Employee Model Fields

| Field | Type | Description |
|-------|------|-------------|
| employee_id | string | Auto-generated (EMP-0001) |
| name | string | Full name |
| email | string | Contact email |
| phone | string | Phone number |
| position | string | Job title |
| department | string | Department |
| hired_at | date | Hire date |
| date_of_birth | date | Date of birth |
| gender | enum | Male / Female |
| address | text | Address |
| salary | decimal | Base salary |
| national_id | string | National ID number |
| emergency_contact | string | Emergency contact |
| bank_account | string | Bank account number |
| contract_type | enum | Permanent / Temporary / Freelance / Intern |
| contract_end_date | date | Contract end date |
| marital_status | enum | Single / Married / Divorced / Widowed |
| nationality | string | Nationality |
| status | enum | Active / Inactive / Suspended / OnLeave |
| photo | media | Spatie media library collection (the `photo` column was dropped in `000004`) |
| user_id | FK | Optional link to User |

## How the enums stay Filament-aware without requiring Filament

`Ikay\JRh\Enums\*` implement `Filament\Support\Contracts\HasLabel`, `HasColor` and `HasIcon` so Filament renders translated labels, badge colours and icons for them. Those interfaces live in `filament/support`, which pulls in Livewire.

`compat/filament-contracts.php` (a Composer `files` autoload entry) declares minimal stand-ins for exactly those three interfaces, **and only when Filament is not installed**. When Filament is present its real contracts win and nothing changes. `tests/Core/CompatShimTest.php` asserts both directions, and `tests/Core/FilamentBoundaryTest.php` fails the build if any core file ever names another Filament or DomPDF symbol.

## Testing

Standalone, against `orchestra/testbench`:

```bash
composer install   # Filament-free, since it is only suggested
composer test
```

The `Filament` suite skips itself when Filament is absent. To exercise the admin layer too:

```bash
composer require --dev "filament/filament:^4.0" "barryvdh/laravel-dompdf:^3.1"
composer test
```

CI runs both combinations.

The package also includes factories for all models. In your own application's test files:

```php
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Ikay\JRh\Models\Advance;

$employee = Employee::factory()->create();
$salary = Salary::factory()->create(['employee_id' => $employee->id]);
$advance = Advance::factory()->approved()->create(['employee_id' => $employee->id]);
```

Publishable host-application tests for the Filament layer still live in `stubs/tests/`:

```bash
php artisan vendor:publish --tag=j-rh-tests
php artisan test --compact tests/Feature/EmployeeTest.php tests/Feature/SalaryTest.php tests/Feature/AdvanceTest.php
```

## License

Proprietary - All rights reserved.
