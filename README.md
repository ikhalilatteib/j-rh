# J-RH - Employee HR Module for Filament v4

A reusable Laravel Filament v4 package for managing employees, salaries, and advances. Designed to replace direct User-based salary/advance tracking with a dedicated Employee model.

## Features

- **Employee Management** - Full CRUD with auto-generated IDs (EMP-0001), extended HR fields (position, department, contract type, etc.)
- **Salary Management** - Monthly salary processing with base salary, primes, advance deductions, and net salary calculation
- **Advance Management** - Employee advance requests with approval workflow and automatic outstanding balance tracking
- **PDF Salary Bulletins** - Generate downloadable salary bulletins via DomPDF
- **Filament Shield Compatible** - Permissions auto-register with `BezhanSalleh/FilamentShield`
- **Multi-language** - French and Arabic translations included
- **User Linking** - Optionally link employees to app users for authentication

## Requirements

- PHP 8.2+
- Laravel 12+
- Filament v4
- barryvdh/laravel-dompdf ^3.1

## Installation

### As a Composer Package

```bash
composer require ikay/j-rh
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

### 1. Register the Filament Plugin

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
| photo | string | Photo path |
| user_id | FK | Optional link to User |

## Testing

The package includes factories for all models. In your test files:

```php
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Ikay\JRh\Models\Advance;

$employee = Employee::factory()->create();
$salary = Salary::factory()->create(['employee_id' => $employee->id]);
$advance = Advance::factory()->approved()->create(['employee_id' => $employee->id]);
```

Run the package tests:

```bash
php artisan test --compact tests/Feature/EmployeeTest.php tests/Feature/SalaryTest.php tests/Feature/AdvanceTest.php
```

## License

Proprietary - All rights reserved.
