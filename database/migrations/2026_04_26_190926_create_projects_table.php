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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('kode_project')->unique();
            $table->string('nama_project');
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('pic_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['brief','pre_prod','production','post_prod','approval','delivery'])->default('brief');
            $table->enum('tipe_iklan', ['TVC','digital','OOH'])->default('TVC');
            $table->date('tanggal_mulai');
            $table->date('tanggal_deadline');
            $table->decimal('budget_total', 15, 2)->default(0);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
