<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Complaint;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Complaint>
 */
class ComplaintFactory extends Factory
{
    protected $model = Complaint::class;

    public function definition(): array
    {
        return [
            'citizen_id' => User::factory(),
            'category_id' => Category::factory(),
            'current_department_id' => Department::factory(),
            'duplicate_of_id' => null,
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(2),
            'status' => 'new',
            'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
            'latitude' => fake()->latitude(15.300000, 15.420000),   // Sana'a area coordinates
            'longitude' => fake()->longitude(44.150000, 44.250000),
        ];
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function duplicateOf(Complaint $complaint): static
    {
        return $this->state(fn (array $attributes) => [
            'duplicate_of_id' => $complaint->id,
            'category_id' => $complaint->category_id,
            'current_department_id' => $complaint->current_department_id,
        ]);
    }
}
