<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('locale', 12)->index();
            $table->string('alt_text', 255)->nullable();
            $table->string('caption', 500)->nullable();
            $table->timestamps();

            $table->unique(['media_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_translations');
    }
};
