<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('schedule_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sc_id')->constrained('schedules')->cascadeOnDelete();
            $table->integer('scd_session');
            $table->time('scd_start_time');
            $table->time('scd_end_time');
            $table->boolean('scd_umum');
            $table->boolean('scd_bpjs');
            $table->integer('scd_counter_max_umum');
            $table->integer('scd_max_umum');
            $table->integer('scd_counter_max_bpjs');
            $table->integer('scd_max_bpjs');
            $table->integer('scd_counter_online_umum');
            $table->integer('scd_online_umum');
            $table->integer('scd_counter_online_bpjs');
            $table->integer('scd_online_bpjs');
            $table->boolean('scd_available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_details');
    }
};
