<?php

declare(strict_types=1);

namespace Ikay\JRh\Tests\Core;

use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Enums\ContractTypeEnum;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Enums\GenderEnum;
use Ikay\JRh\Enums\MaritalStatusEnum;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Ikay\JRh\Policies\AdvancePolicy;
use Ikay\JRh\Policies\EmployeePolicy;
use Ikay\JRh\Policies\SalaryPolicy;
use Ikay\JRh\Tests\TestCase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\Test;

/**
 * The domain core has to behave identically whether or not Filament is installed. Every
 * assertion here runs in both CI jobs; the Filament-free job is the one that proves the
 * split actually worked.
 */
final class CoreWorksWithoutFilamentTest extends TestCase
{
    #[Test]
    public function the_employees_table_is_created_by_the_package(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasTable('employees'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('employees', 'employee_id'));
        $this->assertFalse(
            \Illuminate\Support\Facades\Schema::hasColumn('employees', 'photo'),
            'The photo column is dropped by 2026_03_08_000004; photos live in the media library.',
        );
    }

    /**
     * The package re-keys salaries/advances onto employee_id but never creates those two
     * tables: the host application owns them. Worth pinning, because it is the one piece of
     * schema a new (Filament-free) consumer has to bring itself.
     */
    #[Test]
    public function the_package_ships_no_create_migration_for_salaries_or_advances(): void
    {
        $migrations = array_map(
            'basename',
            (array) glob(dirname(__DIR__, 2).'/database/migrations/*.php'),
        );

        $this->assertNotContains('2026_03_08_000002_create_salaries_table.php', $migrations);

        foreach ($migrations as $migration) {
            $this->assertStringNotContainsString('create_salaries', $migration);
            $this->assertStringNotContainsString('create_advances', $migration);
        }

        $this->assertContains('2026_03_08_000001_create_employees_table.php', $migrations);
    }

    /**
     * On a host whose salaries/advances are already employee-keyed, the two data migrations
     * must detect there is nothing to convert and leave the tables alone.
     */
    #[Test]
    public function the_data_migrations_no_op_when_there_is_nothing_to_convert(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('salaries', 'user_id'));
        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('advances', 'user_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('salaries', 'employee_id'));
        $this->assertTrue(\Illuminate\Support\Facades\Schema::hasColumn('advances', 'employee_id'));
    }

    #[Test]
    public function every_enum_loads_and_labels_itself(): void
    {
        $this->app->setLocale('fr');

        $this->assertSame('En attente', AdvanceStatusEnum::Pending->getLabel());
        $this->assertSame('Approuvé', AdvanceStatusEnum::Approved->getLabel());
        $this->assertSame('En attente', SalaryStatusEnum::Pending->getLabel());
        $this->assertSame('Actif', EmployeeStatusEnum::Active->getLabel());
        $this->assertSame('Masculin', GenderEnum::Male->getLabel());
        $this->assertSame('CDI', ContractTypeEnum::Permanent->getLabel());
        $this->assertNotSame('', MaritalStatusEnum::Single->getLabel());

        $this->app->setLocale('ar');
        $this->assertSame('نشط', EmployeeStatusEnum::Active->getLabel());
    }

    #[Test]
    public function status_enums_still_expose_colours_and_icons(): void
    {
        $this->assertSame('success', EmployeeStatusEnum::Active->getColor());
        $this->assertSame('heroicon-o-check-circle', EmployeeStatusEnum::Active->getIcon());
        $this->assertSame('warning', SalaryStatusEnum::Pending->getColor());
        $this->assertSame('danger', AdvanceStatusEnum::Rejected->getColor());
    }

    #[Test]
    public function an_employee_can_be_created_and_gets_an_auto_generated_matricule(): void
    {
        $employee = Employee::factory()->create();

        $this->assertSame('EMP-'.str_pad((string) $employee->id, 4, '0', STR_PAD_LEFT), $employee->employee_id);
        $this->assertInstanceOf(EmployeeStatusEnum::class, $employee->status);
        $this->assertInstanceOf(GenderEnum::class, $employee->gender);
        $this->assertInstanceOf(ContractTypeEnum::class, $employee->contract_type);
        $this->assertInstanceOf(MaritalStatusEnum::class, $employee->marital_status);
    }

    #[Test]
    public function the_configured_employee_id_prefix_is_honoured(): void
    {
        config()->set('j-rh.employee_id_prefix', 'STAFF');

        $this->assertStringStartsWith('STAFF-', Employee::factory()->create()->employee_id);
    }

    #[Test]
    public function salaries_and_advances_relate_back_to_their_employee(): void
    {
        $employee = Employee::factory()->create();
        $salary = Salary::factory()->for($employee)->create();
        $advance = Advance::factory()->for($employee)->create();

        $this->assertTrue($employee->salaries->contains($salary));
        $this->assertTrue($employee->advances->contains($advance));
        $this->assertTrue($salary->employee->is($employee));
        $this->assertTrue($advance->employee->is($employee));
    }

    #[Test]
    public function net_salary_is_base_plus_prime_minus_deductions(): void
    {
        $salary = Salary::factory()->make([
            'base_salary' => 300000,
            'prime' => 25000,
            'advance_deductions' => 50000,
        ]);

        $salary->calculateNetSalary();

        $this->assertSame('275000.00', (string) $salary->net_salary);
    }

    #[Test]
    public function outstanding_advances_subtract_what_has_already_been_deducted(): void
    {
        $employee = Employee::factory()->create();

        Advance::factory()->for($employee)->approved()->create(['amount' => 100000]);
        Advance::factory()->for($employee)->approved()->create(['amount' => 50000]);
        Advance::factory()->for($employee)->create(['amount' => 999999, 'status' => AdvanceStatusEnum::Pending]);

        $this->assertEqualsWithDelta(150000.0, Advance::totalApproved($employee->id), 0.001);

        Salary::factory()->for($employee)->pending()->create(['advance_deductions' => 40000]);
        Salary::factory()->for($employee)->create([
            'advance_deductions' => 70000,
            'status' => SalaryStatusEnum::Cancelled,
        ]);

        $this->assertEqualsWithDelta(40000.0, Advance::totalDeducted($employee->id), 0.001);
        $this->assertEqualsWithDelta(110000.0, Advance::remainingOutstanding($employee->id), 0.001);
    }

    #[Test]
    public function outstanding_advances_never_go_negative(): void
    {
        $employee = Employee::factory()->create();

        Salary::factory()->for($employee)->pending()->create(['advance_deductions' => 90000]);

        $this->assertSame(0.0, Advance::remainingOutstanding($employee->id));
    }

    #[Test]
    public function the_service_provider_registers_the_policies(): void
    {
        $this->assertInstanceOf(EmployeePolicy::class, Gate::getPolicyFor(Employee::class));
        $this->assertInstanceOf(SalaryPolicy::class, Gate::getPolicyFor(Salary::class));
        $this->assertInstanceOf(AdvancePolicy::class, Gate::getPolicyFor(Advance::class));
    }

    #[Test]
    public function the_package_config_and_translations_are_registered(): void
    {
        $this->assertSame('EMP', config('j-rh.employee_id_prefix'));
        $this->assertSame('XAF', config('j-rh.currency'));
        $this->assertSame('Gestion RH', trans('j-rh::j-rh.hr_management', locale: 'fr'));
    }

    #[Test]
    public function soft_deletes_are_active_on_every_model(): void
    {
        $employee = Employee::factory()->create();
        $employee->delete();

        $this->assertSame(0, Employee::query()->count());
        $this->assertSame(1, Employee::withTrashed()->count());
    }
}
