<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('patient_temporary', function (Blueprint $table) {
            $table->id();
            $table->string('pt_ucode')->unique();
            $table->string('pt_norm')->nullable();
            $table->string('pt_name')->nullable();
            $table->date('pt_birthday')->nullable();
            $table->string('pt_gender')->nullable();
            $table->string('pt_ssn')->nullable();
            $table->string('pt_poli')->nullable();
            $table->string('pt_bpjs')->nullable();
            $table->string('pt_ppk1')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_temporary');
    }
};
