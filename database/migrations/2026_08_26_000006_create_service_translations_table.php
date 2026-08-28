<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('locale', 12)->index();
            $table->string('name', 255);
            $table->string('slug', 255)->index();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->json('benefits')->nullable();
            $table->json('process_steps')->nullable();
            $table->json('faqs')->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_translations');
    }
};
