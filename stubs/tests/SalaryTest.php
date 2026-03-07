<?php

namespace Tests\Feature;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Filament\Resources\SalaryResource;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\CreateSalary;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\EditSalary;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\ListSalaries;
use Ikay\JRh\Filament\Resources\SalaryResource\Pages\ViewSalary;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class SalaryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::factory()->create([
            'status' => UserStatusEnum::ACTIVE,
            'salary' => 150000,
        ]);
        $this->actingAs($this->user);

        $this->employee = Employee::factory()->create([
            'user_id' => $this->user->id,
            'salary' => 150000,
        ]);
    }

    public function test_can_render_salary_list_page(): void
    {
        $this->get(SalaryResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_salary_view_page(): void
    {
        $salary = Salary::factory()->create(['employee_id' => $this->employee->id]);

        $this->get(SalaryResource::getUrl('view', ['record' => $salary]))
            ->assertSuccessful();
    }

    public function test_can_view_salary_infolist(): void
    {
        $salary = Salary::factory()->create([
            'employee_id' => $this->employee->id,
            'base_salary' => 200000,
            'prime' => 25000,
            'advance_deductions' => 5000,
            'net_salary' => 220000,
        ]);

        Livewire::test(ViewSalary::class, ['record' => $salary->getRouteKey()])
            ->assertOk()
            ->assertSchemaStateSet([
                'base_salary' => 200000,
                'prime' => 25000,
                'advance_deductions' => 5000,
                'net_salary' => 220000,
            ]);
    }

    public function test_can_render_salary_create_page(): void
    {
        $this->get(SalaryResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_salary(): void
    {
        $employee = Employee::factory()->create(['salary' => 200000]);

        Advance::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 10000,
            'status' => AdvanceStatusEnum::Approved,
        ]);

        Livewire::test(CreateSalary::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'month' => 3,
                'year' => 2026,
                'base_salary' => 200000,
                'prime' => 25000,
                'advance_deductions' => 5000,
                'status' => SalaryStatusEnum::Pending,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('salaries', [
            'employee_id' => $employee->id,
            'month' => 3,
            'year' => 2026,
            'base_salary' => 200000,
            'prime' => 25000,
            'advance_deductions' => 5000,
            'net_salary' => 220000,
        ]);
    }

    public function test_can_edit_salary(): void
    {
        $salary = Salary::factory()->pending()->create([
            'employee_id' => $this->employee->id,
            'base_salary' => 150000,
            'prime' => 10000,
            'advance_deductions' => 0,
            'net_salary' => 160000,
        ]);

        Livewire::test(EditSalary::class, ['record' => $salary->getRouteKey()])
            ->fillForm([
                'prime' => 20000,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $salary->refresh();
        $this->assertEquals(20000, $salary->prime);
    }

    public function test_can_list_salaries(): void
    {
        $salaries = Salary::factory()->count(3)->create();

        Livewire::test(ListSalaries::class)
            ->assertCanSeeTableRecords($salaries);
    }

    public function test_net_salary_is_calculated_correctly(): void
    {
        $salary = Salary::factory()->create([
            'base_salary' => 300000,
            'prime' => 50000,
            'advance_deductions' => 30000,
            'net_salary' => 300000,
        ]);

        $salary->calculateNetSalary();
        $this->assertEquals(320000, $salary->net_salary);
    }

    public function test_salary_belongs_to_employee(): void
    {
        $salary = Salary::factory()->create(['employee_id' => $this->employee->id]);

        $this->assertInstanceOf(Employee::class, $salary->employee);
        $this->assertEquals($this->employee->id, $salary->employee->id);
    }

    public function test_employee_has_many_salaries(): void
    {
        Salary::factory()->count(3)->create(['employee_id' => $this->employee->id]);

        $this->assertCount(3, $this->employee->salaries);
    }

    public function test_unique_constraint_on_employee_month_year(): void
    {
        Salary::factory()->create([
            'employee_id' => $this->employee->id,
            'month' => 3,
            'year' => 2026,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        Salary::factory()->create([
            'employee_id' => $this->employee->id,
            'month' => 3,
            'year' => 2026,
        ]);
    }

    public function test_advance_deductions_cannot_exceed_salary(): void
    {
        $employee = Employee::factory()->create(['salary' => 100000]);

        Advance::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 500000,
            'status' => AdvanceStatusEnum::Approved,
        ]);

        Livewire::test(CreateSalary::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'month' => 3,
                'year' => 2026,
                'base_salary' => 100000,
                'prime' => 10000,
                'advance_deductions' => 200000,
            ])
            ->call('create')
            ->assertHasFormErrors(['advance_deductions']);
    }

    public function test_advance_deductions_cannot_exceed_outstanding_advances(): void
    {
        $employee = Employee::factory()->create(['salary' => 300000]);

        Advance::factory()->create([
            'employee_id' => $employee->id,
            'amount' => 5000,
            'status' => AdvanceStatusEnum::Approved,
        ]);

        Livewire::test(CreateSalary::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'month' => 3,
                'year' => 2026,
                'base_salary' => 300000,
                'prime' => 0,
                'advance_deductions' => 10000,
            ])
            ->call('create')
            ->assertHasFormErrors(['advance_deductions']);
    }
}
