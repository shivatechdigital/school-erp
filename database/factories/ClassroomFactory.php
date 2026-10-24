<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use LogicException;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => School::query()->value('id')
                ?? throw new LogicException('Create a school before creating a classroom.'),
            'branch_id' => null,
            'room_number' => $this->faker->unique()->bothify('Room ###'),
            'floor' => $this->faker->randomElement(['Ground', 'First', 'Second']),
            'capacity' => $this->faker->numberBetween(20, 40),
            'is_active' => true,
        ];
    }

    public function forSchool(School $school): static
    {
        return $this->state(fn (array $attributes): array => [
            'school_id' => $school->getKey(),
        ]);
    }
}
