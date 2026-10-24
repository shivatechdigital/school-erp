<?php

namespace Tests\Feature;

use App\Filament\Pages\AdminSection\AdmissionQuery;
use App\Filament\Pages\AdminSection\ComplaintManagement;
use App\Filament\Pages\AdminSection\HousesManagement;
use App\Filament\Pages\AdminSection\VisitorBook;
use App\Models\House;
use App\Models\SchoolClass;
use App\Models\StudentIdCard;
use App\Models\User;
use App\Models\VisitorBookEntry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminSectionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_school_admin_can_create_and_edit_a_visitor_entry_with_the_requested_people_count(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        $component = Livewire::test(VisitorBook::class)
            ->fillForm([
                'purpose' => 'Parent meeting',
                'number_of_people' => 2,
                'people' => [
                    ['name' => 'Asha Rao', 'id_number' => 'ID-101'],
                    ['name' => 'Nikhil Rao', 'id_number' => 'ID-102'],
                ],
                'phone' => '9876543210',
                'visit_date' => '2026-10-24',
                'in_time' => '09:30',
                'out_time' => '10:15',
            ])
            ->call('saveRecord')
            ->assertHasNoFormErrors();

        $entry = VisitorBookEntry::query()->firstOrFail();
        $this->assertSame(2, $entry->number_of_people);
        $this->assertSame('Nikhil Rao', $entry->people[1]['name']);
        $this->assertSame('9876543210', $entry->phone);
        $this->assertSame($schoolAdmin->school_id, $entry->school_id);

        $component->call('editRecord', $entry->id)
            ->assertSet('editingRecordId', $entry->id)
            ->set('data.purpose', 'Updated parent meeting')
            ->call('saveRecord')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('visitor_book_entries', [
            'id' => $entry->id,
            'purpose' => 'Updated parent meeting',
        ]);
    }

    public function test_school_admin_can_create_a_complaint_and_a_house_on_the_same_page(): void
    {
        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);

        Livewire::test(ComplaintManagement::class)
            ->fillForm([
                'complainant_name' => 'Parent One',
                'against_name' => 'Transport service',
                'complaint_date' => '2026-10-24',
                'details' => 'The bus arrived late.',
            ])
            ->call('saveRecord')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('complaints', [
            'school_id' => $schoolAdmin->school_id,
            'complainant_name' => 'Parent One',
            'against_name' => 'Transport service',
        ]);

        Livewire::test(HousesManagement::class)
            ->fillForm([
                'name' => 'Green House',
                'color' => '#008000',
                'is_active' => true,
            ])
            ->call('saveRecord')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('houses', [
            'school_id' => $schoolAdmin->school_id,
            'name' => 'Green House',
            'color' => '#008000',
        ]);
        $this->assertInstanceOf(House::class, House::query()->where('name', 'Green House')->firstOrFail());
    }

    public function test_admission_query_is_a_static_page_until_the_website_form_is_connected(): void
    {
        $this->actingAs(User::query()->where('email', 'principal@demo.com')->firstOrFail());

        Livewire::test(AdmissionQuery::class)
            ->assertSee('Website admission inquiries')
            ->assertSee('No inquiries are connected yet.');
    }

    public function test_school_admin_can_download_an_id_card_and_teacher_cannot(): void
    {
        Storage::fake('public');

        $schoolAdmin = User::query()->where('email', 'principal@demo.com')->firstOrFail();
        $this->actingAs($schoolAdmin);
        $class = SchoolClass::query()->firstOrFail();
        $card = StudentIdCard::query()->create([
            'school_id' => $schoolAdmin->school_id,
            'created_by' => $schoolAdmin->id,
            'student_name' => 'Demo Student',
            'photo' => 'admin/id-cards/demo.jpg',
            'class_id' => $class->id,
        ]);

        $this->get(route('admin-section.id-cards.download', $card))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $teacher = User::query()->where('user_type', 'teacher')->firstOrFail();
        $this->actingAs($teacher)
            ->get(route('admin-section.id-cards.download', $card))
            ->assertNotFound();
    }
}
