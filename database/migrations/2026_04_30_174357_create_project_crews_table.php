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
    Schema::create('project_crews', function (Blueprint $table) {
        $table->id();
        $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
        $table->foreignId('crew_id')->constrained('crews')->onDelete('cascade');
        $table->decimal('gaji_per_hari', 15, 2);
        $table->integer('total_hari')->default(1);
        $table->decimal('total_gaji', 15, 2)->storedAs('gaji_per_hari * total_hari');
        $table->enum('status', ['hired', 'completed'])->default('hired');
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('project_crews');
    }
};
