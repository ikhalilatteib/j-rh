<?php

namespace Ikay\JRh\Database\Factories;

use Ikay\JRh\Enums\ContractTypeEnum;
use Ikay\JRh\Enums\EmployeeStatusEnum;
use Ikay\JRh\Enums\GenderEnum;
use Ikay\JRh\Enums\MaritalStatusEnum;
use Ikay\JRh\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Employee> */
class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'position' => fake()->jobTitle(),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Sales', 'Operations']),
            'hired_at' => fake()->dateTimeBetween('-5 years', 'now'),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'gender' => fake()->randomElement(GenderEnum::cases()),
            'address' => fake()->address(),
            'salary' => fake()->numberBetween(50000, 500000),
            'national_id' => fake()->optional()->numerify('##########'),
            'emergency_contact' => fake()->optional()->phoneNumber(),
            'bank_account' => fake()->optional()->bankAccountNumber(),
            'contract_type' => fake()->randomElement(ContractTypeEnum::cases()),
            'contract_end_date' => fake()->optional()->dateTimeBetween('now', '+3 years'),
            'marital_status' => fake()->randomElement(MaritalStatusEnum::cases()),
            'nationality' => fake()->country(),
            'status' => EmployeeStatusEnum::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EmployeeStatusEnum::Inactive,
        ]);
    }
}
