<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Booking;
use App\Models\Media;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePrice;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_relationships(): void
    {
        $category = ServiceCategory::factory()->create();
        $hero = Media::factory()->create();
        $service = Service::factory()->create([
            'service_category_id' => $category->id,
            'hero_media_id' => $hero->id,
        ]);
        $price = ServicePrice::factory()->create(['service_id' => $service->id]);
        $booking = Booking::factory()->create([
            'service_id' => $service->id,
            'service_price_id' => $price->id,
        ]);

        $this->assertTrue($service->category->is($category));
        $this->assertTrue($service->heroMedia->is($hero));
        $this->assertTrue($service->prices->contains($price));
        $this->assertTrue($service->bookings->contains($booking));
    }

    public function test_training_course_relationships(): void
    {
        $hero = Media::factory()->create();
        $course = TrainingCourse::factory()->create(['hero_media_id' => $hero->id]);
        $inquiry = TrainingInquiry::factory()->create(['training_course_id' => $course->id]);

        $this->assertTrue($course->heroMedia->is($hero));
        $this->assertTrue($course->inquiries->contains($inquiry));
    }

    public function test_post_relationships(): void
    {
        $category = PostCategory::factory()->create();
        $author = User::factory()->create();
        $hero = Media::factory()->create();
        $post = Post::factory()->create([
            'post_category_id' => $category->id,
            'author_id' => $author->id,
            'hero_media_id' => $hero->id,
        ]);

        $this->assertTrue($post->category->is($category));
        $this->assertTrue($post->author->is($author));
        $this->assertTrue($post->heroMedia->is($hero));
        $this->assertTrue($author->authoredPosts->contains($post));
    }

    public function test_user_uploaded_media_relationship(): void
    {
        $user = User::factory()->create();
        $media = Media::factory()->create(['uploaded_by' => $user->id]);

        $this->assertTrue($user->uploadedMedia->contains($media));
        $this->assertTrue($media->uploader->is($user));
    }
}
