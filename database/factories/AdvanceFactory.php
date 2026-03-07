<?php

namespace Ikay\JRh\Database\Factories;

use Ikay\JRh\Enums\AdvanceStatusEnum;
use Ikay\JRh\Models\Advance;
use Ikay\JRh\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Advance> */
class AdvanceFactory extends Factory
{
    protected $model = Advance::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'amount' => fake()->numberBetween(10000, 200000),
            'date' => fake()->date(),
            'reason' => fake()->optional()->sentence(),
            'status' => AdvanceStatusEnum::Pending,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AdvanceStatusEnum::Approved,
        ]);
    }
}
