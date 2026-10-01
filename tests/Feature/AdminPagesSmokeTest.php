<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected string $seeder = DatabaseSeeder::class;

    public function test_every_filament_resource_page_renders_for_super_admin(): void
    {
        $this->assertPagesRender(User::query()->where('email', 'admin@schoolorp.com')->firstOrFail());
    }

    public function test_every_filament_resource_page_renders_for_school_admin(): void
    {
        $this->assertPagesRender(User::query()->where('email', 'principal@demo.com')->firstOrFail());
    }

    private function assertPagesRender(User $user): void
    {
        $this->actingAs($user);

        $failures = [];

        foreach (Filament::getPanel('admin')->getResources() as $resource) {
            if (! $resource::canViewAny()) {
                continue;
            }

            $urls = ['index' => $resource::getUrl('index')];

            if ($resource::hasPage('create') && $resource::canCreate()) {
                $urls['create'] = $resource::getUrl('create');
            }

            $record = $resource::getEloquentQuery()->first();

            foreach (['view', 'edit'] as $page) {
                if ($record && $resource::hasPage($page)) {
                    $urls[$page] = $resource::getUrl($page, ['record' => $record]);
                }
            }

            foreach ($urls as $page => $url) {
                $response = $this->get($url);

                if ($response->status() !== 200) {
                    $message = $response->exception?->getMessage() ?? 'HTTP '.$response->status();
                    $failures[] = class_basename($resource)." [{$page}]: ".$message;
                }
            }
        }

        $this->assertSame([], $failures, "Broken admin pages:\n".implode("\n", $failures));
    }
}
