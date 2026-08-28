<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('service_id')->constrained('services')->restrictOnDelete();
            $table->foreignId('service_price_id')->nullable()->constrained('service_prices')->restrictOnDelete();
            $table->string('service_name_snapshot', 255)->nullable();
            $table->string('service_price_label_snapshot', 150)->nullable();
            $table->unsignedInteger('duration_minutes_snapshot')->nullable();
            $table->unsignedBigInteger('price_amount_snapshot')->nullable();
            $table->string('customer_name', 150);
            $table->string('phone', 30);
            $table->string('phone_normalized', 30)->index();
            $table->string('email', 255)->nullable();
            $table->date('preferred_date')->index();
            $table->time('preferred_time');
            $table->unsignedInteger('guest_count')->default(1);
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->string('status', 32)->default('NEW')->index();
            $table->string('locale', 12)->default('vi')->index();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('utm_source', 100)->nullable()->index();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 150)->nullable()->index();
            $table->string('utm_content', 150)->nullable();
            $table->string('utm_term', 150)->nullable();
            $table->string('gclid', 150)->nullable();
            $table->string('gbraid', 100)->nullable();
            $table->string('wbraid', 100)->nullable();
            $table->string('fbclid', 150)->nullable();
            $table->string('fbp', 100)->nullable();
            $table->string('fbc', 100)->nullable();
            $table->string('landing_page', 500)->nullable();
            $table->string('referrer', 500)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'preferred_date']);
            $table->index(['service_id', 'status']);
            $table->index(['utm_source', 'utm_campaign', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
