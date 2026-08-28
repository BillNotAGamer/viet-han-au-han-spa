<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Services;

use App\Enums\ContentStatus;
use App\Models\Booking;
use App\Models\Media;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Services\ServiceCatalog\ServiceWriter;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_service_admin(): void
    {
        $this->get('/admin/services')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/services')->assertStatus(403);
    }

    public function test_admin_can_access_service_admin_pages(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/admin/services')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/services/create')->assertStatus(200);
    }

    public function test_service_writer_creates_service_atomically_with_prices_and_gallery(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();
        $heroMedia = Media::factory()->create();
        $galleryMedia = Media::factory()->count(2)->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'hero_media_id' => $heroMedia->id,
            'status' => ContentStatus::PUBLISHED,
            'is_featured' => true,
            'sort_order' => 10,
            'vi' => [
                'name' => 'Massage Trị Liệu Đá Nóng',
                'slug' => 'massage-tri-lieu-da-nong',
                'excerpt' => 'Giảm đau nhức cơ bắp toàn thân',
                'content' => '<p>Liệu trình 90 phút kết hợp đá bazan tự nhiên</p>',
                'benefits' => [
                    ['title' => 'Lưu thông khí huyết', 'description' => 'Nhiệt lượng từ đá bazan kích hoạt tuần hoàn máu'],
                ],
                'process_steps' => [
                    ['title' => 'Bước 1: Khởi động', 'description' => 'Thư giãn cơ thể bằng tinh dầu thảo dược'],
                ],
                'faqs' => [
                    ['question' => 'Phù hợp với ai?', 'answer' => 'Người mỏi cơ, căng thẳng, stress kéo dài'],
                ],
                'seo_title' => 'Massage Trị Liệu Đá Nóng Chuẩn Hàn',
                'seo_description' => 'Trị liệu toàn thân bằng đá nóng',
            ],
            'en' => [
                'name' => 'Hot Stone Therapy Massage',
                'slug' => 'hot-stone-therapy-massage',
                'excerpt' => 'Full body muscle relief',
                'content' => '<p>90 minutes therapy with natural basalt stones</p>',
            ],
            'prices' => [
                [
                    'duration_minutes' => 60,
                    'price_amount' => 390000,
                    'sort_order' => 1,
                    'is_active' => true,
                    'label_vi' => 'Gói Tiêu Chuẩn 60 Phút',
                    'label_en' => 'Standard 60 Mins',
                ],
                [
                    'duration_minutes' => 90,
                    'price_amount' => 550000,
                    'sort_order' => 2,
                    'is_active' => true,
                    'label_vi' => 'Gói Chuyên Sâu 90 Phút',
                    'label_en' => 'Intensive 90 Mins',
                ],
            ],
            'gallery_media_ids' => $galleryMedia->pluck('id')->toArray(),
        ]);

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'service_category_id' => $category->id,
            'hero_media_id' => $heroMedia->id,
            'status' => 'PUBLISHED',
            'is_featured' => 1,
            'sort_order' => 10,
        ]);

        $this->assertDatabaseHas('service_translations', [
            'service_id' => $service->id,
            'locale' => 'vi',
            'name' => 'Massage Trị Liệu Đá Nóng',
            'slug' => 'massage-tri-lieu-da-nong',
        ]);

        $this->assertDatabaseHas('service_translations', [
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Hot Stone Therapy Massage',
            'slug' => 'hot-stone-therapy-massage',
        ]);

        $this->assertCount(2, $service->prices);
        $this->assertDatabaseHas('service_prices', [
            'service_id' => $service->id,
            'duration_minutes' => 60,
            'price_amount' => 390000,
        ]);
        $this->assertDatabaseHas('service_prices', [
            'service_id' => $service->id,
            'duration_minutes' => 90,
            'price_amount' => 550000,
        ]);

        $this->assertCount(2, $service->media);
    }

    public function test_service_translation_id_is_preserved_across_updates(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => [
                'name' => 'Tên Gốc',
                'slug' => 'ten-goc',
                'excerpt' => 'Tóm tắt gốc',
            ],
            'en' => [
                'name' => 'Original Name',
                'slug' => 'original-name',
            ],
        ]);

        $viTrans = $service->translationFor('vi');
        $enTrans = $service->translationFor('en');

        $this->assertNotNull($viTrans);
        $this->assertNotNull($enTrans);

        $originalViId = $viTrans->id;
        $originalEnId = $enTrans->id;

        // Perform update
        $updatedService = $writer->update($service, [
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => [
                'name' => 'Tên Mới Cập Nhật',
                'slug' => 'ten-moi-cap-nhat',
                'excerpt' => 'Tóm tắt mới',
            ],
            'en' => [
                'name' => 'Updated Name',
                'slug' => 'updated-name',
            ],
        ]);

        $newViTrans = $updatedService->translationFor('vi');
        $newEnTrans = $updatedService->translationFor('en');

        // IDs MUST remain stable
        $this->assertSame($originalViId, $newViTrans->id);
        $this->assertSame($originalEnId, $newEnTrans->id);
        $this->assertSame('Tên Mới Cập Nhật', $newViTrans->name);
        $this->assertSame('Updated Name', $newEnTrans->name);
    }

    public function test_service_archive_and_restore_workflow(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Gội Đầu Dưỡng Sinh', 'slug' => 'goi-dau-duong-sinh'],
        ]);

        $this->assertSame(ContentStatus::PUBLISHED, $service->status);

        // Archive
        $writer->archive($service);
        $this->assertSame(ContentStatus::ARCHIVED, $service->fresh()->status);

        // Restore
        $writer->restore($service);
        $this->assertSame(ContentStatus::DRAFT, $service->fresh()->status);
    }

    public function test_draft_service_without_bookings_can_be_deleted(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Dịch Vụ Nháp Tạm', 'slug' => 'dich-vu-nhap-tam'],
            'prices' => [
                ['duration_minutes' => 30, 'price_amount' => 150000],
            ],
        ]);

        $serviceId = $service->id;
        $writer->delete($service);

        $this->assertDatabaseMissing('services', ['id' => $serviceId]);
        $this->assertDatabaseMissing('service_translations', ['service_id' => $serviceId]);
        $this->assertDatabaseMissing('service_prices', ['service_id' => $serviceId]);
    }

    public function test_draft_service_with_bookings_cannot_be_deleted(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => ['name' => 'Trị Mụn Chuyên Sâu', 'slug' => 'tri-mun-chuyen-sau'],
        ]);

        Booking::factory()->create(['service_id' => $service->id]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Cannot delete service because it has associated bookings.');

        $writer->delete($service);
    }

    public function test_published_service_without_bookings_cannot_be_deleted(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::PUBLISHED,
            'vi' => ['name' => 'Dịch Vụ Đã Xuất Bản', 'slug' => 'dich-vu-da-xuat-ban'],
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only draft services can be deleted.');

        $writer->delete($service);
    }

    public function test_archived_service_without_bookings_cannot_be_deleted(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::ARCHIVED,
            'vi' => ['name' => 'Dịch Vụ Đã Lưu Trữ', 'slug' => 'dich-vu-da-luu-tru'],
        ]);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Only draft services can be deleted.');

        $writer->delete($service);
    }

    public function test_empty_json_repeaters_persist_consistently_as_null(): void
    {
        $writer = app(ServiceWriter::class);
        $category = ServiceCategory::factory()->create();

        $service = $writer->create([
            'service_category_id' => $category->id,
            'status' => ContentStatus::DRAFT,
            'vi' => [
                'name' => 'Dịch Vụ Không Structured Data',
                'slug' => 'dich-vu-khong-structured-data',
                'benefits' => [],
                'process_steps' => null,
                'faqs' => [['question' => '', 'answer' => '']], // empty entries
            ],
        ]);

        $translation = $service->translationFor('vi');
        $this->assertNull($translation->benefits);
        $this->assertNull($translation->process_steps);
        $this->assertNull($translation->faqs);
    }
}
