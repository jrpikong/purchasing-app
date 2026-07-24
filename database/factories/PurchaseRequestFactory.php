<?php

namespace Database\Factories;

use App\Enums\Priority;
use App\Enums\PurchaseRequestStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'department_id' => Department::factory(),
            'request_date' => now()->toDateString(),
            'required_date' => now()->addWeek()->toDateString(),
            'purpose' => fake()->sentence(10),
            'total_amount' => 0,
            'status' => PurchaseRequestStatus::DRAFT,
            'priority' => Priority::MEDIUM,
        ];
    }
}
