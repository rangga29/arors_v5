<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fisioterapi_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sd_id')->constrained('schedule_dates')->cascadeOnDelete();
            $table->string('fap_ucode')->unique();
            $table->string('fap_token');
            $table->string('fap_type');
            $table->string('fap_queue');
            $table->time('fap_registration_time');
            $table->time('fap_appointment_time');
            $table->string('fap_norm');
            $table->string('fap_name');
            $table->date('fap_birthday');
            $table->string('fap_gender');
            $table->string('fap_phone');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fisio_appointments');
    }
};
