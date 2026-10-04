<?php

namespace Tests\Feature;

use App\Filament\Pages\AcademicSessionSettings;
use App\Filament\Pages\HolidayCalendar;
use App\Models\AcademicYear;
use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AcademicSessionAndHolidayCalendarTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_school_admin_can_update_the_academic_session(): void
    {
        $principal = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($principal);

        Livewire::test(AcademicSessionSettings::class)
            ->mountAction('editSession', arguments: [])
            ->setActionData([
                'start_month' => 4,
                'start_year' => 2026,
                'end_month' => 3,
                'end_year' => 2027,
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $session = AcademicYear::query()->where('school_id', $principal->school_id)->where('is_current', true)->firstOrFail();

        $this->assertSame('2026-04-01', $session->start_date->toDateString());
        $this->assertSame('2027-03-31', $session->end_date->toDateString());
        $this->assertSame(1, AcademicYear::query()->where('school_id', $principal->school_id)->where('is_current', true)->count());
    }

    public function test_adding_editing_and_removing_a_holiday_via_calendar_click(): void
    {
        $principal = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($principal);

        $session = AcademicYear::query()->where('school_id', $principal->school_id)->where('is_current', true)->firstOrFail();
        $date = $session->start_date->copy()->addDays(5)->toDateString();

        $component = Livewire::test(HolidayCalendar::class)
            ->mountAction('manageHoliday', arguments: ['date' => $date]);

        $component->setActionData(['holiday_name' => 'Diwali', 'description' => 'School closed'])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('Diwali', Holiday::query()->where('school_id', $principal->school_id)->whereDate('date', $date)->firstOrFail()->title);

        // Editing pre-fills the existing holiday.
        Livewire::test(HolidayCalendar::class)
            ->mountAction('manageHoliday', arguments: ['date' => $date])
            ->setActionData(['holiday_name' => 'Diwali Break', 'description' => null])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertSame('Diwali Break', Holiday::query()->where('school_id', $principal->school_id)->whereDate('date', $date)->firstOrFail()->title);

        // Blanking the title removes the holiday.
        Livewire::test(HolidayCalendar::class)
            ->mountAction('manageHoliday', arguments: ['date' => $date])
            ->setActionData(['holiday_name' => '', 'description' => null])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertFalse(Holiday::query()->where('school_id', $principal->school_id)->whereDate('date', $date)->exists());
    }

    public function test_month_navigation_is_bounded_by_the_academic_session(): void
    {
        $principal = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($principal);

        $session = AcademicYear::query()->where('school_id', $principal->school_id)->where('is_current', true)->firstOrFail();

        $component = Livewire::test(HolidayCalendar::class)
            ->set('month', $session->start_date->copy()->startOfMonth()->format('Y-m'));

        $component->call('previousMonth');
        $component->assertSet('month', $session->start_date->copy()->startOfMonth()->format('Y-m'));

        $component->set('month', $session->end_date->copy()->startOfMonth()->format('Y-m'));
        $component->call('nextMonth');
        $component->assertSet('month', $session->end_date->copy()->startOfMonth()->format('Y-m'));
    }
}
