<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedule_dates', function (Blueprint $table) {
            $table->id();
            $table->string('sd_ucode')->unique();
            $table->date('sd_date');
            $table->boolean('sd_is_downloaded')->default(false);
            $table->boolean('sd_is_holiday')->default(false);
            $table->string('sd_holiday_desc')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_dates');
    }
};
