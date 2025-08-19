<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('new_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ap_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('nap_norm');
            $table->string('nap_name');
            $table->date('nap_birthday');
            $table->string('nap_phone');
            $table->string('nap_ssn');
            $table->string('nap_gender');
            $table->text('nap_address');
            $table->string('nap_email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('new_appointments');
    }
};
