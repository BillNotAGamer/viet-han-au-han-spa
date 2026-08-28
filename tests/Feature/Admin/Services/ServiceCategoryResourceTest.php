<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\ServiceCatalog\ServiceCategoryWriter;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_service_category_admin(): void
    {
        $response = $this->get('/admin/service-categories');
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    public function test_non_admin_cannot_access_service_category_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $response = $this->actingAs($user)->get('/admin/service-categories');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_service_category_list_and_create_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/service-categories')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/service-categories/create')->assertStatus(200);
    }

    public function test_category_writer_creates_core_and_bilingual_translations(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 5,
            'vi' => [
                'name' => 'Chăm Sóc Da Mặt',
                'slug' => 'cham-soc-da-mat',
                'description' => 'Mô tả chăm sóc da mặt chuyên sâu',
                'seo_title' => 'Chăm Sóc Da Mặt Uy Tín',
                'seo_description' => 'Dịch vụ chăm sóc da mặt chuẩn Hàn Quốc',
            ],
            'en' => [
                'name' => 'Facial Care',
                'slug' => 'facial-care',
                'description' => 'Intensive facial care therapy',
                'seo_title' => 'Facial Care Spa',
                'seo_description' => 'Korean standard facial care',
            ],
        ]);

        $this->assertInstanceOf(ServiceCategory::class, $category);
        $this->assertSame(ContentStatus::PUBLISHED, $category->status);
        $this->assertSame(5, $category->sort_order);

        $this->assertDatabaseHas('service_categories', [
            'id' => $category->id,
            'status' => 'PUBLISHED',
            'sort_order' => 5,
        ]);

        $this->assertDatabaseHas('service_category_translations', [
            'service_category_id' => $category->id,
            'locale' => 'vi',
            'name' => 'Chăm Sóc Da Mặt',
            'slug' => 'cham-soc-da-mat',
        ]);

        $this->assertDatabaseHas('service_category_translations', [
            'service_category_id' => $category->id,
            'locale' => 'en',
            'name' => 'Facial Care',
            'slug' => 'facial-care',
        ]);
    }

    public function test_category_update_modifies_existing_translation_without_recreating_row(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::DRAFT,
            'sort_order' => 1,
            'vi' => [
                'name' => 'Massage Body',
                'slug' => 'massage-body',
            ],
        ]);

        $viTranslation = $category->translationFor('vi');
        $this->assertNotNull($viTranslation);
        $originalTranslationId = $viTranslation->id;

        $updated = $writer->update($category, [
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 2,
            'vi' => [
                'name' => 'Massage Body Chuyên Sâu',
                'slug' => 'massage-body-chuyen-sau',
            ],
        ]);

        $this->assertSame($category->id, $updated->id);
        $this->assertSame(ContentStatus::PUBLISHED, $updated->status);
        $this->assertSame(2, $updated->sort_order);

        $newViTranslation = $updated->translationFor('vi');
        $this->assertSame($originalTranslationId, $newViTranslation->id);
        $this->assertSame('Massage Body Chuyên Sâu', $newViTranslation->name);
        $this->assertSame('massage-body-chuyen-sau', $newViTranslation->slug);
    }

    public function test_category_containing_services_cannot_be_deleted(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
            'vi' => [
                'name' => 'Trị Liệu Cổ Vai Gáy',
                'slug' => 'tri-lieu-co-vai-gay',
            ],
        ]);

        Service::factory()->create([
            'service_category_id' => $category->id,
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete category because it contains associated services.');

        $writer->delete($category);
    }

    public function test_category_without_services_can_be_deleted(): void
    {
        $writer = app(ServiceCategoryWriter::class);

        $category = $writer->create([
            'status' => ContentStatus::DRAFT,
            'sort_order' => 1,
            'vi' => [
                'name' => 'Danh Mục Tạm',
                'slug' => 'danh-muc-tam',
            ],
        ]);

        $categoryId = $category->id;
        $writer->delete($category);

        $this->assertDatabaseMissing('service_categories', ['id' => $categoryId]);
        $this->assertDatabaseMissing('service_category_translations', ['service_category_id' => $categoryId]);
    }
}
