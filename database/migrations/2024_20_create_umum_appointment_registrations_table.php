<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('umum_appointment_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uap_id')->constrained('umum_appointments')->cascadeOnDelete();
            $table->string('uar_ucode')->unique();
            $table->string('uar_no');
            $table->date('uar_date');
            $table->string('uar_session');
            $table->time('uar_time');
            $table->string('uar_reg_no');
            $table->string('uar_reg_status');
            $table->string('uar_queue');
            $table->string('uar_room');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umum_appointment_registrations');
    }
};
