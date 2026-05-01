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
    Schema::create('crews', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->enum('role', ['Sutradara', 'DoP', 'Art Director'])->default('Sutradara');
        $table->string('email')->unique();
        $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::dropIfExists('crews');
    }
};
