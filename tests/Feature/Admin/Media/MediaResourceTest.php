<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Media;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_media_resource(): void
    {
        $this->get('/admin/media')->assertStatus(302);
    }

    public function test_non_admin_cannot_access_media_resource(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/media')->assertStatus(403);
    }

    public function test_admin_can_access_media_list_and_create(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/media')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/media/create')->assertStatus(200);
    }

    public function test_admin_can_access_media_edit_page(): void
    {
        $admin = User::factory()->admin()->create();
        $media = Media::factory()->create(['disk' => 'public']);

        $this->actingAs($admin)->get("/admin/media/{$media->id}/edit")->assertStatus(200);
    }

    public function test_missing_physical_file_does_not_crash_admin_pages_or_delete_database_record(): void
    {
        $admin = User::factory()->admin()->create();
        Storage::fake('public');

        // Create Media DB record whose physical file does NOT exist on disk
        $media = Media::factory()->create([
            'disk' => 'public',
            'path' => 'media/2026/08/non_existent_file.jpg',
            'file_name' => 'non_existent_file.jpg',
        ]);

        Storage::disk('public')->assertMissing('media/2026/08/non_existent_file.jpg');

        // Access List page -> 200 OK
        $this->actingAs($admin)->get('/admin/media')->assertStatus(200);

        // Access Edit page -> 200 OK
        $this->actingAs($admin)->get("/admin/media/{$media->id}/edit")->assertStatus(200);

        // Media DB record still exists (not silently deleted)
        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }
}
