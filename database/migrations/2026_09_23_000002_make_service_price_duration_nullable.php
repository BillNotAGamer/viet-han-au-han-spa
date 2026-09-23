<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_prices', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        if (DB::table('service_prices')->whereNull('duration_minutes')->exists()) {
            throw new LogicException('Cannot safely restore required service price durations while unknown durations exist.');
        }

        Schema::table('service_prices', function (Blueprint $table) {
            $table->unsignedInteger('duration_minutes')->default(60)->change();
        });
    }
};
