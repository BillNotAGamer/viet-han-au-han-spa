<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_price_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_price_id')->constrained('service_prices')->cascadeOnDelete();
            $table->string('locale', 12)->index();
            $table->string('label', 150);
            $table->timestamps();

            $table->unique(['service_price_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_price_translations');
    }
};
