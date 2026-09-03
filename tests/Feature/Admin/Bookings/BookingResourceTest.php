<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Bookings;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Policies\BookingPolicy;
use App\Services\Booking\BookingWorkflow;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_and_non_admin_cannot_access_booking_resource(): void
    {
        $this->get('/admin/bookings')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/bookings')->assertStatus(403);
    }

    public function test_admin_can_access_booking_list_and_view_but_not_create_or_edit_pages(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create();

        $this->actingAs($admin)->get('/admin/bookings')->assertStatus(200);
        $this->actingAs($admin)->get("/admin/bookings/{$booking->id}")->assertStatus(200);
        $this->actingAs($admin)->get('/admin/bookings/create')->assertStatus(404);
        $this->actingAs($admin)->get("/admin/bookings/{$booking->id}/edit")->assertStatus(404);
    }

    public function test_booking_workflow_can_contact_confirm_cancel_and_save_admin_note(): void
    {
        $workflow = app(BookingWorkflow::class);
        $booking = Booking::factory()->create(['status' => BookingStatus::NEW]);

        $workflow->markAsContacted($booking, 'Called customer');
        $fresh = $booking->fresh();
        $this->assertSame(BookingStatus::CONTACTED, $fresh->status);
        $this->assertNotNull($fresh->contacted_at);
        $this->assertSame('Called customer', $fresh->admin_note);

        $workflow->markAsConfirmed($fresh, 'Confirmed appointment');
        $fresh = $booking->fresh();
        $this->assertSame(BookingStatus::CONFIRMED, $fresh->status);
        $this->assertNotNull($fresh->confirmed_at);
        $this->assertSame('Confirmed appointment', $fresh->admin_note);

        $workflow->updateAdminNote($fresh, 'Desk note only');
        $this->assertSame('Desk note only', $booking->fresh()->admin_note);

        $cancellable = Booking::factory()->create(['status' => BookingStatus::CONTACTED]);
        $workflow->markAsCancelled($cancellable, 'Customer cancelled');
        $this->assertSame(BookingStatus::CANCELLED, $cancellable->fresh()->status);
        $this->assertNotNull($cancellable->fresh()->cancelled_at);
    }

    public function test_invalid_booking_workflow_transitions_are_rejected(): void
    {
        $workflow = app(BookingWorkflow::class);
        $completed = Booking::factory()->create(['status' => BookingStatus::COMPLETED]);

        $this->expectException(DomainException::class);
        $workflow->markAsCancelled($completed);
    }

    public function test_admin_note_updates_preserve_customer_request_fields(): void
    {
        $workflow = app(BookingWorkflow::class);
        $service = Service::factory()->create();
        $booking = Booking::factory()->create([
            'service_id' => $service->id,
            'service_price_id' => null,
            'reference' => 'BK-20260901-KEEP01',
            'customer_name' => 'Customer Keep',
            'phone' => '0901234567',
            'phone_normalized' => '+84901234567',
            'email' => 'keep@example.test',
            'preferred_date' => '2026-09-03',
            'preferred_time' => '10:30',
            'customer_note' => 'Original customer note',
            'locale' => 'vi',
            'status' => BookingStatus::NEW,
            'admin_note' => null,
        ]);

        $originalCreatedAt = $booking->created_at;
        $workflow->updateAdminNote($booking, 'Internal follow-up');
        $fresh = $booking->fresh();

        $this->assertSame('Internal follow-up', $fresh->admin_note);
        $this->assertSame('BK-20260901-KEEP01', $fresh->reference);
        $this->assertSame('Customer Keep', $fresh->customer_name);
        $this->assertSame('0901234567', $fresh->phone);
        $this->assertSame('+84901234567', $fresh->phone_normalized);
        $this->assertSame('keep@example.test', $fresh->email);
        $this->assertSame('2026-09-03', $fresh->preferred_date->format('Y-m-d'));
        $this->assertSame('10:30', $fresh->preferred_time);
        $this->assertSame('Original customer note', $fresh->customer_note);
        $this->assertSame('vi', $fresh->locale);
        $this->assertSame(BookingStatus::NEW, $fresh->status);
        $this->assertEquals($originalCreatedAt, $fresh->created_at);
    }

    public function test_policy_prevents_admin_created_or_destructive_booking_management(): void
    {
        $admin = User::factory()->admin()->create();
        $booking = Booking::factory()->create();
        $policy = new BookingPolicy;

        $this->assertTrue($policy->viewAny($admin));
        $this->assertTrue($policy->view($admin, $booking));
        $this->assertTrue($policy->update($admin, $booking));
        $this->assertTrue($policy->restore($admin, $booking));
        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->delete($admin, $booking));
        $this->assertFalse($policy->forceDelete($admin, $booking));
        $this->assertFalse($policy->forceDeleteAny($admin));
    }
}
