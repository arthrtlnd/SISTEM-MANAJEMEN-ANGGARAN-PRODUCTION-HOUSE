<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('nama_klien');
            $table->string('industri')->nullable();
            $table->string('kontak_person')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->enum('tipe', ['retainer', 'per_project'])->default('per_project');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
