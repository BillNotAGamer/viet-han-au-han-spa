<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Booking;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Post;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use App\Models\ServiceTranslation;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForeignKeyDeleteBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_service_cascades_translations_and_prices(): void
    {
        $service = Service::factory()->create();
        $translation = ServiceTranslation::factory()->create(['service_id' => $service->id]);
        $price = ServicePrice::factory()->create(['service_id' => $service->id]);
        $priceTranslation = ServicePriceTranslation::factory()->create(['service_price_id' => $price->id]);

        $service->delete();

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
        $this->assertDatabaseMissing('service_translations', ['id' => $translation->id]);
        $this->assertDatabaseMissing('service_prices', ['id' => $price->id]);
        $this->assertDatabaseMissing('service_price_translations', ['id' => $priceTranslation->id]);
    }

    public function test_deleting_page_cascades_translations(): void
    {
        $page = Page::factory()->create();
        $translation = PageTranslation::factory()->create(['page_id' => $page->id]);

        $page->delete();

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
        $this->assertDatabaseMissing('page_translations', ['id' => $translation->id]);
    }

    public function test_deleting_service_category_with_services_is_restricted(): void
    {
        $category = ServiceCategory::factory()->create();
        Service::factory()->create(['service_category_id' => $category->id]);

        $this->expectException(QueryException::class);
        $category->delete();
    }

    public function test_deleting_service_with_booking_is_restricted(): void
    {
        $service = Service::factory()->create();
        Booking::factory()->create(['service_id' => $service->id]);

        $this->expectException(QueryException::class);
        $service->delete();
    }

    public function test_deleting_service_price_with_booking_is_restricted(): void
    {
        $price = ServicePrice::factory()->create();
        Booking::factory()->create([
            'service_id' => $price->service_id,
            'service_price_id' => $price->id,
        ]);

        $this->expectException(QueryException::class);
        $price->delete();
    }

    public function test_deleting_training_course_with_inquiry_is_restricted(): void
    {
        $course = TrainingCourse::factory()->create();
        TrainingInquiry::factory()->create(['training_course_id' => $course->id]);

        $this->expectException(QueryException::class);
        $course->delete();
    }

    public function test_deleting_media_used_as_service_hero_is_restricted(): void
    {
        $media = Media::factory()->create();
        Service::factory()->create(['hero_media_id' => $media->id]);

        $this->expectException(QueryException::class);
        $media->delete();
    }

    public function test_deleting_user_sets_post_author_to_null(): void
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['author_id' => $user->id]);

        $user->delete();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'author_id' => null]);
    }

    public function test_deleting_user_sets_media_uploaded_by_to_null(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['uploaded_by' => $user->id]);

        $user->delete();

        $this->assertDatabaseHas('media', ['id' => $media->id, 'uploaded_by' => null]);
    }
}
