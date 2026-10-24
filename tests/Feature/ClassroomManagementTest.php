<?php

namespace Tests\Feature;

use App\Filament\Resources\Classrooms\Pages\CreateClassroom;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClassroomManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_school_admin_can_create_a_classroom_with_a_floor_and_capacity(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        Livewire::test(CreateClassroom::class)
            ->fillForm([
                'room_number' => 'A-101',
                'floor' => 'First Floor',
                'capacity' => 36,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('classrooms', [
            'school_id' => $schoolAdmin->school_id,
            'room_number' => 'A-101',
            'floor' => 'First Floor',
            'capacity' => 36,
            'is_active' => true,
        ]);
    }
}
