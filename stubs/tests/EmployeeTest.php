<?php

namespace Tests\Feature;

use App\Enums\UserStatusEnum;
use App\Models\User;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Filament\Resources\EmployeeResource;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\CreateEmployee;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\EditEmployee;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\ListEmployees;
use Ikay\JRh\Filament\Resources\EmployeeResource\Pages\ViewEmployee;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::before(fn () => true);

        $this->user = User::factory()->create([
            'status' => UserStatusEnum::ACTIVE,
        ]);
        $this->actingAs($this->user);
    }

    public function test_can_render_employee_list_page(): void
    {
        $this->get(EmployeeResource::getUrl('index'))
            ->assertSuccessful();
    }

    public function test_can_render_employee_create_page(): void
    {
        $this->get(EmployeeResource::getUrl('create'))
            ->assertSuccessful();
    }

    public function test_can_create_employee(): void
    {
        Livewire::test(CreateEmployee::class)
            ->fillForm([
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+237600000000',
                'position' => 'Developer',
                'department' => 'IT',
                'hired_at' => '2026-01-15',
                'salary' => 250000,
                'status' => EmployeeStatusEnum::Active,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('employees', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'position' => 'Developer',
            'department' => 'IT',
        ]);
    }

    public function test_employee_id_is_auto_generated(): void
    {
        $employee = Employee::factory()->create();

        $this->assertNotNull($employee->employee_id);
        $this->assertStringStartsWith('EMP-', $employee->employee_id);
    }

    public function test_employee_id_is_unique(): void
    {
        $employee1 = Employee::factory()->create();
        $employee2 = Employee::factory()->create();

        $this->assertNotEquals($employee1->employee_id, $employee2->employee_id);
    }

    public function test_can_render_employee_view_page(): void
    {
        $employee = Employee::factory()->create();

        $this->get(EmployeeResource::getUrl('view', ['record' => $employee]))
            ->assertSuccessful();
    }

    public function test_can_view_employee_infolist(): void
    {
        $employee = Employee::factory()->create([
            'name' => 'Jane Doe',
            'position' => 'Manager',
        ]);

        Livewire::test(ViewEmployee::class, ['record' => $employee->getRouteKey()])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => 'Jane Doe',
                'position' => 'Manager',
            ]);
    }

    public function test_can_edit_employee(): void
    {
        $employee = Employee::factory()->create(['name' => 'Old Name']);

        Livewire::test(EditEmployee::class, ['record' => $employee->getRouteKey()])
            ->fillForm([
                'name' => 'New Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $employee->refresh();
        $this->assertEquals('New Name', $employee->name);
    }

    public function test_can_list_employees(): void
    {
        $employees = Employee::factory()->count(3)->create();

        Livewire::test(ListEmployees::class)
            ->assertCanSeeTableRecords($employees);
    }

    public function test_employee_belongs_to_user(): void
    {
        $employee = Employee::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(User::class, $employee->user);
        $this->assertEquals($this->user->id, $employee->user->id);
    }

    public function test_employee_can_exist_without_user(): void
    {
        $employee = Employee::factory()->create(['user_id' => null]);

        $this->assertNull($employee->user);
    }

    public function test_employee_has_many_salaries(): void
    {
        $employee = Employee::factory()->create();
        Salary::factory()->count(3)->create(['employee_id' => $employee->id]);

        $this->assertCount(3, $employee->salaries);
    }

    public function test_employee_has_many_advances(): void
    {
        $employee = Employee::factory()->create();
        Advance::factory()->count(3)->create(['employee_id' => $employee->id]);

        $this->assertCount(3, $employee->advances);
    }

    public function test_user_has_employee_relationship(): void
    {
        $employee = Employee::factory()->create(['user_id' => $this->user->id]);

        $this->assertInstanceOf(Employee::class, $this->user->employee);
        $this->assertEquals($employee->id, $this->user->employee->id);
    }
}
