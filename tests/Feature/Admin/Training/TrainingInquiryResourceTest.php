<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Training;

use App\Enums\TrainingInquiryStatus;
use App\Models\TrainingCourse;
use App\Models\TrainingInquiry;
use App\Models\User;
use App\Policies\TrainingInquiryPolicy;
use App\Services\Training\TrainingInquiryWorkflow;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrainingInquiryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_training_inquiry_status_backing_values_are_exact(): void
    {
        $cases = TrainingInquiryStatus::cases();

        $this->assertCount(4, $cases);
        $values = array_map(fn (TrainingInquiryStatus $status) => $status->value, $cases);

        $this->assertSame(['NEW', 'CONTACTED', 'ENROLLED', 'CLOSED'], $values);
    }

    public function test_guest_and_non_admin_cannot_access_inquiries(): void
    {
        $this->get('/admin/training-inquiries')->assertStatus(302);

        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user)->get('/admin/training-inquiries')->assertStatus(403);
    }

    public function test_admin_can_access_inquiry_list_and_view(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = TrainingInquiry::factory()->create();

        $this->actingAs($admin)->get('/admin/training-inquiries')->assertStatus(200);
        $this->actingAs($admin)->get("/admin/training-inquiries/{$inquiry->id}")->assertStatus(200);
    }

    public function test_no_create_or_generic_edit_route_is_exposed(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = TrainingInquiry::factory()->create();

        // Create page does not exist (404)
        $this->actingAs($admin)->get('/admin/training-inquiries/create')->assertStatus(404);

        // Edit page does not exist (404)
        $this->actingAs($admin)->get("/admin/training-inquiries/{$inquiry->id}/edit")->assertStatus(404);
    }

    public function test_workflow_new_to_contacted(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);
        $inquiry = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::NEW]);

        $workflow->markAsContacted($inquiry, 'Đã gọi điện tư vấn học phí');

        $fresh = $inquiry->fresh();
        $this->assertSame(TrainingInquiryStatus::CONTACTED, $fresh->status);
        $this->assertNotNull($fresh->contacted_at);
        $this->assertSame('Đã gọi điện tư vấn học phí', $fresh->admin_note);
    }

    public function test_workflow_contacted_to_enrolled(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);
        $inquiry = TrainingInquiry::factory()->create([
            'status' => TrainingInquiryStatus::CONTACTED,
            'contacted_at' => now()->subDay(),
        ]);

        $workflow->markAsEnrolled($inquiry, 'Học viên đã đóng cọc nhập học');

        $fresh = $inquiry->fresh();
        $this->assertSame(TrainingInquiryStatus::ENROLLED, $fresh->status);
        $this->assertNotNull($fresh->enrolled_at);
        $this->assertNotNull($fresh->contacted_at); // Preserved
        $this->assertSame('Học viên đã đóng cọc nhập học', $fresh->admin_note);
    }

    public function test_workflow_new_and_contacted_to_closed(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);

        // 1. From NEW to CLOSED
        $inq1 = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::NEW]);
        $workflow->markAsClosed($inq1, 'Sai số điện thoại');
        $this->assertSame(TrainingInquiryStatus::CLOSED, $inq1->fresh()->status);
        $this->assertNotNull($inq1->fresh()->closed_at);

        // 2. From CONTACTED to CLOSED
        $inq2 = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::CONTACTED]);
        $workflow->markAsClosed($inq2, 'Học viên không có nhu cầu nữa');
        $this->assertSame(TrainingInquiryStatus::CLOSED, $inq2->fresh()->status);
        $this->assertNotNull($inq2->fresh()->closed_at);
    }

    public function test_invalid_status_transitions_are_rejected_and_state_remains_unchanged(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);

        // NEW -> ENROLLED (Must be contacted first)
        $inqNew = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::NEW]);
        try {
            $workflow->markAsEnrolled($inqNew);
            $this->fail('Expected DomainException on invalid transition');
        } catch (DomainException $e) {
            // Success
        }

        $fresh = $inqNew->fresh();
        $this->assertSame(TrainingInquiryStatus::NEW, $fresh->status);
        $this->assertNull($fresh->contacted_at);
        $this->assertNull($fresh->enrolled_at);
        $this->assertNull($fresh->closed_at);
    }

    public function test_terminal_enrolled_transition_is_rejected(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);

        $inqEnrolled = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::ENROLLED]);
        $this->expectException(DomainException::class);
        $workflow->markAsClosed($inqEnrolled);
    }

    public function test_terminal_closed_transition_is_rejected(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);

        $inqClosed = TrainingInquiry::factory()->create(['status' => TrainingInquiryStatus::CLOSED]);
        $this->expectException(DomainException::class);
        $workflow->markAsContacted($inqClosed);
    }

    public function test_admin_note_isolation_preserves_all_lead_and_attribution_fields(): void
    {
        $workflow = app(TrainingInquiryWorkflow::class);
        $course = TrainingCourse::factory()->create();

        $inquiry = TrainingInquiry::create([
            'reference' => 'TRN-20260827-ISO999',
            'training_course_id' => $course->id,
            'customer_name' => 'Nguyễn Thị Bích Thảo',
            'phone' => '0912345678',
            'phone_normalized' => '+84912345678',
            'email' => 'bichthao@example.com',
            'message' => 'Cần tư vấn khóa học spa cấp tốc',
            'status' => TrainingInquiryStatus::CONTACTED,
            'locale' => 'vi',
            'admin_note' => 'Ghi chú ban đầu',
            'contacted_at' => '2026-08-27 10:00:00',
            'enrolled_at' => null,
            'closed_at' => null,
            'utm_source' => 'facebook',
            'utm_medium' => 'cpc',
            'utm_campaign' => 'academy_vip_2026',
            'utm_content' => 'video_clip_02',
            'utm_term' => 'khoa hoc spa',
            'gclid' => 'gclid_sample_123',
            'gbraid' => 'gbraid_sample_456',
            'wbraid' => 'wbraid_sample_789',
            'fbclid' => 'fbclid_sample_999',
            'fbp' => 'fbp_sample_001',
            'fbc' => 'fbc_sample_002',
            'landing_page' => 'https://viethanauhanspa.com/dao-tao-hoc-vien',
            'referrer' => 'https://facebook.com/ad',
        ]);

        $originalCreatedAt = $inquiry->created_at;

        // Perform admin note update via the workflow service used by Filament action
        $workflow->updateAdminNote($inquiry, 'Ghi chú đã được cập nhật bởi quản trị viên');

        $fresh = $inquiry->fresh();

        // 1. Note changed
        $this->assertSame('Ghi chú đã được cập nhật bởi quản trị viên', $fresh->admin_note);

        // 2. Submission fields unchanged
        $this->assertSame('TRN-20260827-ISO999', $fresh->reference);
        $this->assertSame($course->id, $fresh->training_course_id);
        $this->assertSame('Nguyễn Thị Bích Thảo', $fresh->customer_name);
        $this->assertSame('0912345678', $fresh->phone);
        $this->assertSame('+84912345678', $fresh->phone_normalized);
        $this->assertSame('bichthao@example.com', $fresh->email);
        $this->assertSame('Cần tư vấn khóa học spa cấp tốc', $fresh->message);
        $this->assertSame('vi', $fresh->locale);
        $this->assertEquals($originalCreatedAt, $fresh->created_at);

        // 3. Status and lifecycle timestamps unchanged
        $this->assertSame(TrainingInquiryStatus::CONTACTED, $fresh->status);
        $this->assertSame('2026-08-27 10:00:00', $fresh->contacted_at?->format('Y-m-d H:i:s'));
        $this->assertNull($fresh->enrolled_at);
        $this->assertNull($fresh->closed_at);

        // 4. Attribution unchanged
        $this->assertSame('facebook', $fresh->utm_source);
        $this->assertSame('cpc', $fresh->utm_medium);
        $this->assertSame('academy_vip_2026', $fresh->utm_campaign);
        $this->assertSame('video_clip_02', $fresh->utm_content);
        $this->assertSame('khoa hoc spa', $fresh->utm_term);
        $this->assertSame('gclid_sample_123', $fresh->gclid);
        $this->assertSame('gbraid_sample_456', $fresh->gbraid);
        $this->assertSame('wbraid_sample_789', $fresh->wbraid);
        $this->assertSame('fbclid_sample_999', $fresh->fbclid);
        $this->assertSame('fbp_sample_001', $fresh->fbp);
        $this->assertSame('fbc_sample_002', $fresh->fbc);
        $this->assertSame('https://viethanauhanspa.com/dao-tao-hoc-vien', $fresh->landing_page);
        $this->assertSame('https://facebook.com/ad', $fresh->referrer);
    }

    public function test_soft_delete_and_restore(): void
    {
        $inquiry = TrainingInquiry::factory()->create();

        // Normal query sees inquiry
        $this->assertTrue(TrainingInquiry::where('id', $inquiry->id)->exists());

        // Soft delete
        $inquiry->delete();
        $this->assertFalse(TrainingInquiry::where('id', $inquiry->id)->exists());
        $this->assertTrue(TrainingInquiry::withTrashed()->where('id', $inquiry->id)->exists());

        // Restore
        $inquiry->restore();
        $this->assertTrue(TrainingInquiry::where('id', $inquiry->id)->exists());
        $this->assertNull($inquiry->fresh()->deleted_at);
    }

    public function test_policy_strictly_prohibits_creation_and_force_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $inquiry = TrainingInquiry::factory()->create();
        $policy = new TrainingInquiryPolicy;

        $this->assertFalse($policy->create($admin));
        $this->assertFalse($policy->forceDelete($admin, $inquiry));
        $this->assertFalse($policy->forceDeleteAny($admin));
        $this->assertTrue($policy->delete($admin, $inquiry));
        $this->assertTrue($policy->restore($admin, $inquiry));
    }
}
