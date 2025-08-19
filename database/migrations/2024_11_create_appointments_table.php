<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scd_id')->constrained('schedule_details')->cascadeOnDelete();
            $table->string('ap_ucode')->unique();
            $table->string('ap_no');
            $table->string('ap_token');
            $table->string('ap_queue');
            $table->string('ap_type');
            $table->time('ap_registration_time');
            $table->time('ap_appointment_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
