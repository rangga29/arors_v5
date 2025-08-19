<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('qr_caroluses', function (Blueprint $table) {
            $table->id();
            $table->string('qrc_ucode')->unique();
            $table->string('qrc_room');
            $table->string('qrc_password');
            $table->boolean('qrc_active')->default(true);
            $table->bigInteger('qrc_counter')->default(0);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_caroluses');
    }
};
