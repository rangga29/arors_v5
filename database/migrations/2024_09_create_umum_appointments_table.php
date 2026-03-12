<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('umum_appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ap_id')->constrained('appointments')->cascadeOnDelete();
            $table->string('uap_norm');
            $table->string('uap_name');
            $table->date('uap_birthday');
            $table->string('uap_gender');
            $table->string('uap_phone');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('umum_appointments');
    }
};
