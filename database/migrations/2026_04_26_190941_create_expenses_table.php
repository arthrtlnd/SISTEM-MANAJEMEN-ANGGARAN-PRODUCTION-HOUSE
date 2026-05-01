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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->string('nama_pengeluaran');
            $table->enum('kategori', ['sewa_lokasi','honorarium','peralatan','katering','transportasi','lain_lain']);
            $table->decimal('jumlah', 15, 2);
            $table->text('keterangan')->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->date('tanggal_pengeluaran');
            $table->string('bukti_file')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
