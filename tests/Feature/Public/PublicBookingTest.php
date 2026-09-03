<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Enums\BookingStatus;
use App\Enums\ContentStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePrice;
use App\Models\ServicePriceTranslation;
use App\Models\ServiceTranslation;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PublicBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_routes_render_and_vi_prefixed_route_is_not_canonical(): void
    {
        $this->get('/dat-lich')->assertStatus(200);
        $this->get('/en/booking')->assertStatus(200);
        $this->get('/vi')->assertStatus(301)->assertRedirect('/');
        $this->get('/vi/dat-lich')->assertStatus(404);
    }

    public function test_valid_vi_and_en_submissions_use_prg_and_route_derived_locale(): void
    {
        $viService = $this->createServiceWithTranslation('vi', 'VI Booking Service', 'vi-booking-service');
        $enService = $this->createServiceWithTranslation('en', 'EN Booking Service', 'en-booking-service');

        $viPayload = $this->validPayload($viService->id, [
            'customer_name' => 'Nguyen Van A',
            'locale' => 'en',
            'status' => BookingStatus::CONFIRMED->value,
            'admin_note' => 'hacked',
        ]);

        $this->from('/dat-lich')
            ->post('/dat-lich', $viPayload)
            ->assertRedirect('/dat-lich')
            ->assertSessionHas('booking_status', __('booking.success', [], 'vi'));

        $this->assertDatabaseCount('bookings', 1);
        $viBooking = Booking::first();
        $this->assertSame(BookingStatus::NEW, $viBooking->status);
        $this->assertSame('vi', $viBooking->locale);
        $this->assertSame('Nguyen Van A', $viBooking->customer_name);
        $this->assertSame('090 123 4567', $viBooking->phone);
        $this->assertSame('+84901234567', $viBooking->phone_normalized);
        $this->assertNull($viBooking->admin_note);

        $this->from('/en/booking')
            ->post('/en/booking', $this->validPayload($enService->id, ['customer_name' => 'English Guest']))
            ->assertRedirect('/en/booking')
            ->assertSessionHas('booking_status', __('booking.success', [], 'en'));

        $this->assertDatabaseCount('bookings', 2);
        $this->assertSame('en', Booking::latest('id')->first()->locale);
    }

    public function test_following_success_redirect_does_not_create_duplicate_booking(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'PRG Service', 'prg-service');

        $response = $this->followingRedirects()
            ->post('/dat-lich', $this->validPayload($service->id));

        $response
            ->assertStatus(200)
            ->assertSee(__('booking.success', [], 'vi'));

        $this->assertDatabaseCount('bookings', 1);
        $this->get('/dat-lich')->assertStatus(200);
        $this->assertDatabaseCount('bookings', 1);
    }

    public function test_booking_validation_rejects_missing_or_invalid_input_without_persisting(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Validation Service', 'validation-service');

        $payload = $this->validPayload($service->id, [
            'customer_name' => '',
            'phone' => '',
            'email' => 'not-an-email',
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->subDay()->toDateString(),
            'preferred_time' => '25:99',
            'notes' => str_repeat('x', 2001),
            'consent' => null,
        ]);

        $this->from('/dat-lich')
            ->post('/dat-lich', $payload)
            ->assertRedirect('/dat-lich')
            ->assertSessionHasErrors([
                'customer_name',
                'phone',
                'email',
                'preferred_date',
                'preferred_time',
                'notes',
                'consent',
            ]);

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_today_and_future_dates_are_valid_but_past_date_is_rejected(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Date Service', 'date-service');
        $today = CarbonImmutable::now('Asia/Ho_Chi_Minh')->toDateString();
        $future = CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString();
        $past = CarbonImmutable::now('Asia/Ho_Chi_Minh')->subDay()->toDateString();

        $this->post('/dat-lich', $this->validPayload($service->id, ['preferred_date' => $today]))
            ->assertRedirect('/dat-lich');
        $this->post('/dat-lich', $this->validPayload($service->id, ['preferred_date' => $future]))
            ->assertRedirect('/dat-lich');
        $this->from('/dat-lich')
            ->post('/dat-lich', $this->validPayload($service->id, ['preferred_date' => $past]))
            ->assertRedirect('/dat-lich')
            ->assertSessionHasErrors('preferred_date');

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_service_options_and_submission_require_exact_locale_public_services(): void
    {
        $viService = $this->createServiceWithTranslation('vi', 'Visible VI Booking Option', 'visible-vi-booking-option');
        $this->createServiceWithTranslation('en', 'English Only Booking Option', 'english-only-booking-option');

        $this->get('/dat-lich')
            ->assertStatus(200)
            ->assertSee('Visible VI Booking Option')
            ->assertDontSee('English Only Booking Option');

        $this->get('/en/booking')
            ->assertStatus(200)
            ->assertSee('English Only Booking Option')
            ->assertDontSee('Visible VI Booking Option');

        $this->post('/dat-lich', $this->validPayload($viService->id))
            ->assertRedirect('/dat-lich');

        ServiceTranslation::create([
            'service_id' => $viService->id,
            'locale' => 'en',
            'name' => 'Exact EN Booking Option',
            'slug' => 'exact-en-booking-option',
        ]);

        $this->post('/en/booking', $this->validPayload($viService->id))
            ->assertRedirect('/en/booking');

        $this->assertDatabaseCount('bookings', 2);
        $this->assertSame('Exact EN Booking Option', Booking::latest('id')->first()->service_name_snapshot);
    }

    public function test_service_tampering_is_rejected_for_non_public_wrong_locale_and_missing_services(): void
    {
        $draft = $this->createServiceWithTranslation('vi', 'Draft Booking Service', 'draft-booking-service', ContentStatus::DRAFT);
        $archived = $this->createServiceWithTranslation('vi', 'Archived Booking Service', 'archived-booking-service', ContentStatus::ARCHIVED);
        $enOnly = $this->createServiceWithTranslation('en', 'EN Only Tamper Service', 'en-only-tamper-service');

        foreach ([$draft->id, $archived->id, $enOnly->id, 999999] as $serviceId) {
            $this->from('/dat-lich')
                ->post('/dat-lich', $this->validPayload($serviceId))
                ->assertRedirect('/dat-lich')
                ->assertSessionHasErrors('service_id');
        }

        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_booking_submission_snapshots_exact_locale_service_and_price_data(): void
    {
        $service = $this->createServiceWithTranslation('vi', 'Snapshot VI Service', 'snapshot-vi-service');
        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => 'en',
            'name' => 'Snapshot EN Service',
            'slug' => 'snapshot-en-service',
        ]);

        $price = ServicePrice::create([
            'service_id' => $service->id,
            'duration_minutes' => 90,
            'price_amount' => 690000,
            'sort_order' => 1,
            'is_active' => true,
        ]);
        ServicePriceTranslation::create([
            'service_price_id' => $price->id,
            'locale' => 'en',
            'label' => 'Exact EN Price Label',
        ]);

        $this->post('/en/booking', $this->validPayload($service->id))
            ->assertRedirect('/en/booking');

        $booking = Booking::first();
        $this->assertSame('Snapshot EN Service', $booking->service_name_snapshot);
        $this->assertSame('Exact EN Price Label', $booking->service_price_label_snapshot);
        $this->assertSame(90, $booking->duration_minutes_snapshot);
        $this->assertSame(690000, $booking->price_amount_snapshot);
    }

    public function test_booking_post_is_rate_limited(): void
    {
        RateLimiter::clear('203.0.113.44');
        $service = $this->createServiceWithTranslation('vi', 'Rate Limit Service', 'rate-limit-service');

        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.44'])
                ->post('/dat-lich', $this->validPayload($service->id, ['phone' => '09012345'.$i]))
                ->assertRedirect('/dat-lich');
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.44'])
            ->post('/dat-lich', $this->validPayload($service->id, ['phone' => '090123459']))
            ->assertStatus(429);
    }

    public function test_public_booking_privacy_boundary_and_no_contact_mutation_routes(): void
    {
        $publicMutations = collect(Route::getRoutes())
            ->filter(fn ($route) => array_intersect(['POST', 'PUT', 'PATCH', 'DELETE'], $route->methods()) !== [])
            ->reject(fn ($route) => str_starts_with($route->uri(), 'admin'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'livewire'))
            ->reject(fn ($route) => str_starts_with($route->uri(), 'storage'))
            ->values();

        $this->assertSame(['dat-lich', 'en/booking'], $publicMutations->pluck('uri')->sort()->values()->all());
        $this->assertTrue($publicMutations->every(fn ($route) => in_array('POST', $route->methods(), true)));

        $this->get('/dat-lich/1')->assertStatus(404);
        $this->get('/en/booking/1')->assertStatus(404);
        $this->post('/lien-he')->assertStatus(405);
        $this->post('/en/contact')->assertStatus(405);
    }

    public function test_booking_ctas_point_to_booking_routes_without_primary_nav_changes(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('href="'.route('vi.booking.create').'"', false)
            ->assertSee('href="'.route('vi.services.index').'"', false)
            ->assertSee('href="'.route('vi.training.index').'"', false)
            ->assertSee('href="'.route('vi.blog.index').'"', false)
            ->assertDontSee('href="#contact-preview" class="public-floating-booking__link"', false);

        $this->get('/en')
            ->assertStatus(200)
            ->assertSee('href="'.route('en.booking.create').'"', false)
            ->assertSee('href="'.route('en.services.index').'"', false)
            ->assertSee('href="'.route('en.training.index').'"', false)
            ->assertSee('href="'.route('en.blog.index').'"', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    protected function validPayload(int $serviceId, array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'Test Guest',
            'phone' => '090 123 4567',
            'email' => 'guest@example.test',
            'service_id' => $serviceId,
            'preferred_date' => CarbonImmutable::now('Asia/Ho_Chi_Minh')->addDay()->toDateString(),
            'preferred_time' => '14:30',
            'notes' => 'Quiet room preferred.',
            'consent' => '1',
        ], $overrides);
    }

    protected function createServiceWithTranslation(
        string $locale,
        string $name,
        string $slug,
        ContentStatus $status = ContentStatus::PUBLISHED
    ): Service {
        $category = ServiceCategory::create([
            'status' => ContentStatus::PUBLISHED,
            'sort_order' => 1,
        ]);

        $service = Service::create([
            'service_category_id' => $category->id,
            'hero_media_id' => null,
            'status' => $status,
            'is_featured' => false,
            'sort_order' => 1,
        ]);

        ServiceTranslation::create([
            'service_id' => $service->id,
            'locale' => $locale,
            'name' => $name,
            'slug' => $slug,
            'excerpt' => $name.' excerpt',
            'content' => '<p>'.$name.' content paragraph.</p>',
        ]);

        return $service->fresh(['translations']);
    }
}
