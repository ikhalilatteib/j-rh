<?php

namespace Tests\Feature;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Filament\Resources\AdvanceResource;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\CreateAdvance;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\EditAdvance;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\ListAdvances;
use Ikay\JRh\Filament\Resources\AdvanceResource\Pages\ViewAdvance;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class AdvanceTest extends TestCase
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
        ]);
        $this->actingAs($this->user);

        $this->employee = Employee::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_render_advance_list_page(): void
    {
        $this->get(AdvanceResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_advance_view_page(): void
    {
        $advance = Advance::factory()->create(['employee_id' => $this->employee->id]);

        $this->get(AdvanceResource::getUrl('view', ['record' => $advance]))
            ->assertSuccessful();
    }

    public function test_can_view_advance_infolist(): void
    {
        $advance = Advance::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 50000,
            'reason' => 'Emergency expenses',
        ]);

        Livewire::test(ViewAdvance::class, ['record' => $advance->getRouteKey()])
            ->assertOk()
            ->assertSchemaStateSet([
                'amount' => 50000,
                'reason' => 'Emergency expenses',
            ]);
    }

    public function test_can_render_advance_create_page(): void
    {
        $this->get(AdvanceResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_advance(): void
    {
        $employee = Employee::factory()->create();

        Livewire::test(CreateAdvance::class)
            ->fillForm([
                'employee_id' => $employee->id,
                'amount' => 50000,
                'date' => '2026-03-05',
                'reason' => 'Emergency expenses',
                'status' => AdvanceStatusEnum::Pending,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('advances', [
            'employee_id' => $employee->id,
            'amount' => 50000,
            'reason' => 'Emergency expenses',
        ]);
    }

    public function test_can_edit_advance(): void
    {
        $advance = Advance::factory()->create([
            'employee_id' => $this->employee->id,
            'amount' => 50000,
            'status' => AdvanceStatusEnum::Pending,
        ]);

        Livewire::test(EditAdvance::class, ['record' => $advance->getRouteKey()])
            ->fillForm([
                'reason' => 'Updated reason',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $advance->refresh();
        $this->assertEquals('Updated reason', $advance->reason);
    }

    public function test_can_list_advances(): void
    {
        $advances = Advance::factory()->count(3)->create();

        Livewire::test(ListAdvances::class)
            ->assertCanSeeTableRecords($advances);
    }

    public function test_advance_belongs_to_employee(): void
    {
        $advance = Advance::factory()->create(['employee_id' => $this->employee->id]);

        $this->assertInstanceOf(Employee::class, $advance->employee);
        $this->assertEquals($this->employee->id, $advance->employee->id);
    }

    public function test_employee_has_many_advances(): void
    {
        Advance::factory()->count(3)->create(['employee_id' => $this->employee->id]);

        $this->assertCount(3, $this->employee->advances);
    }

    public function test_total_approved_returns_sum_of_approved_advances(): void
    {
        Advance::factory()->create(['employee_id' => $this->employee->id, 'amount' => 20000, 'status' => AdvanceStatusEnum::Approved]);
        Advance::factory()->create(['employee_id' => $this->employee->id, 'amount' => 30000, 'status' => AdvanceStatusEnum::Approved]);
        Advance::factory()->create(['employee_id' => $this->employee->id, 'amount' => 10000, 'status' => AdvanceStatusEnum::Pending]);

        $this->assertEquals(50000, Advance::totalApproved($this->employee->id));
    }

    public function test_total_deducted_returns_sum_from_non_cancelled_salaries(): void
    {
        Salary::factory()->create(['employee_id' => $this->employee->id, 'advance_deductions' => 15000, 'status' => SalaryStatusEnum::Paid]);
        Salary::factory()->create(['employee_id' => $this->employee->id, 'advance_deductions' => 5000, 'status' => SalaryStatusEnum::Pending]);
        Salary::factory()->create(['employee_id' => $this->employee->id, 'advance_deductions' => 10000, 'status' => SalaryStatusEnum::Cancelled]);

        $this->assertEquals(20000, Advance::totalDeducted($this->employee->id));
    }

    public function test_remaining_outstanding_subtracts_deducted_from_approved(): void
    {
        Advance::factory()->create(['employee_id' => $this->employee->id, 'amount' => 50000, 'status' => AdvanceStatusEnum::Approved]);
        Salary::factory()->create(['employee_id' => $this->employee->id, 'advance_deductions' => 20000, 'status' => SalaryStatusEnum::Paid]);

        $this->assertEquals(30000, Advance::remainingOutstanding($this->employee->id));
    }

    public function test_remaining_outstanding_never_goes_negative(): void
    {
        Advance::factory()->create(['employee_id' => $this->employee->id, 'amount' => 10000, 'status' => AdvanceStatusEnum::Approved]);
        Salary::factory()->create(['employee_id' => $this->employee->id, 'advance_deductions' => 15000, 'status' => SalaryStatusEnum::Paid]);

        $this->assertEquals(0, Advance::remainingOutstanding($this->employee->id));
    }
}
