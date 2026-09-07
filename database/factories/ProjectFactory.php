<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $targetAmount = fake()->randomFloat(2, 50000, 500000);

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'department_id' => Department::factory(),
            'target_amount' => $targetAmount,
            'current_amount' => 0.00,
            'status' => 'published',
            'latitude' => fake()->latitude(15.300000, 15.420000),
            'longitude' => fake()->longitude(44.150000, 44.250000),
            'created_by' => User::factory(),
        ];
    }
}
