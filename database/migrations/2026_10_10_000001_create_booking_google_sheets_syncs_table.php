<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_google_sheets_syncs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->restrictOnDelete();
            $table->string('booking_reference', 32);
            $table->string('destination', 64);
            $table->string('status', 32)->index();
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('last_attempted_at')->nullable();
            $table->timestamp('next_attempt_at')->nullable()->index();
            $table->timestamp('synced_at')->nullable();
            $table->string('last_error_code', 64)->nullable();
            $table->string('last_error_summary', 500)->nullable();
            $table->boolean('append_safe')->default(false);
            $table->boolean('retryable')->default(false);
            $table->boolean('requires_manual_intervention')->default(false);
            $table->uuid('lease_token')->nullable()->unique();
            $table->timestamp('lease_expires_at')->nullable()->index();
            $table->timestamps();

            $table->unique(
                ['booking_id', 'destination'],
                'booking_gs_sync_booking_destination_unique',
            );
            $table->unique(
                ['booking_reference', 'destination'],
                'booking_gs_sync_reference_destination_unique',
            );
            $table->index(
                ['status', 'retryable', 'next_attempt_at'],
                'booking_gs_sync_eligibility_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_google_sheets_syncs');
    }
};
