<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->string('cl_ucode')->unique();
            $table->string('cl_code');
            $table->string('cl_code_bpjs');
            $table->string('cl_name');
            $table->bigInteger('cl_order');
            $table->boolean('cl_umum')->default(true);
            $table->boolean('cl_bpjs')->default(true);
            $table->boolean('cl_active')->default(true);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinics');
    }
};
