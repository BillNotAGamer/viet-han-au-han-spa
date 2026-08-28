<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_course_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_course_id')->constrained('training_courses')->cascadeOnDelete();
            $table->string('locale', 12)->index();
            $table->string('title', 255);
            $table->string('slug', 255)->index();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('duration_display', 100)->nullable();
            $table->string('schedule_display', 100)->nullable();
            $table->text('target_audience')->nullable();
            $table->json('curriculum_modules')->nullable();
            $table->json('benefits')->nullable();
            $table->json('faqs')->nullable();
            $table->string('seo_title', 255)->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamps();

            $table->unique(['training_course_id', 'locale']);
            $table->unique(['locale', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_course_translations');
    }
};
