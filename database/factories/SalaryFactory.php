<?php

namespace Ikay\JRh\Database\Factories;

use Ikay\JRh\Enums\SalaryStatusEnum;
use Ikay\JRh\Models\Employee;
use Ikay\JRh\Models\Salary;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Salary> */
class SalaryFactory extends Factory
{
    protected $model = Salary::class;

    public function definition(): array
    {
        $baseSalary = fake()->numberBetween(50000, 500000);
        $prime = fake()->numberBetween(0, 50000);
        $advanceDeductions = fake()->numberBetween(0, 30000);

        return [
            'employee_id' => Employee::factory(),
            'month' => fake()->numberBetween(1, 12),
            'year' => fake()->numberBetween(2024, 2026),
            'base_salary' => $baseSalary,
            'prime' => $prime,
            'advance_deductions' => $advanceDeductions,
            'net_salary' => $baseSalary + $prime - $advanceDeductions,
            'status' => fake()->randomElement(SalaryStatusEnum::cases()),
            'paid_at' => fake()->optional()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalaryStatusEnum::Paid,
            'paid_at' => now(),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SalaryStatusEnum::Pending,
            'paid_at' => null,
        ]);
    }
}
