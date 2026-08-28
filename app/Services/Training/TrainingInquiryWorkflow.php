<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\TrainingInquiryStatus;
use App\Models\TrainingInquiry;
use DomainException;
use Illuminate\Support\Facades\DB;

class TrainingInquiryWorkflow
{
    public function markAsContacted(TrainingInquiry $inquiry, ?string $adminNote = null): void
    {
        if ($inquiry->status !== TrainingInquiryStatus::NEW) {
            throw new DomainException("Cannot transition from {$inquiry->status->value} to CONTACTED. Only NEW inquiries can be marked as contacted.");
        }

        DB::transaction(function () use ($inquiry, $adminNote) {
            $payload = [
                'status' => TrainingInquiryStatus::CONTACTED,
                'contacted_at' => $inquiry->contacted_at ?? now(),
            ];

            if ($adminNote !== null) {
                $payload['admin_note'] = $adminNote;
            }

            $inquiry->update($payload);
        });
    }

    public function markAsEnrolled(TrainingInquiry $inquiry, ?string $adminNote = null): void
    {
        if ($inquiry->status !== TrainingInquiryStatus::CONTACTED) {
            throw new DomainException("Cannot transition from {$inquiry->status->value} to ENROLLED. Inquiry must be in CONTACTED status first.");
        }

        DB::transaction(function () use ($inquiry, $adminNote) {
            $payload = [
                'status' => TrainingInquiryStatus::ENROLLED,
                'enrolled_at' => now(),
            ];

            if ($adminNote !== null) {
                $payload['admin_note'] = $adminNote;
            }

            $inquiry->update($payload);
        });
    }

    public function markAsClosed(TrainingInquiry $inquiry, ?string $adminNote = null): void
    {
        if ($inquiry->status === TrainingInquiryStatus::ENROLLED || $inquiry->status === TrainingInquiryStatus::CLOSED) {
            throw new DomainException("Cannot close an already terminal inquiry in {$inquiry->status->value} status.");
        }

        DB::transaction(function () use ($inquiry, $adminNote) {
            $payload = [
                'status' => TrainingInquiryStatus::CLOSED,
                'closed_at' => now(),
            ];

            if ($adminNote !== null) {
                $payload['admin_note'] = $adminNote;
            }

            $inquiry->update($payload);
        });
    }

    public function updateAdminNote(TrainingInquiry $inquiry, string $note): void
    {
        $inquiry->update(['admin_note' => $note]);
    }
}
