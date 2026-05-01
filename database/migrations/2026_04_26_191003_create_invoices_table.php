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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice')->unique();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('nama_vendor');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal_invoice');
            $table->date('jatuh_tempo');
            $table->enum('status', ['belum_bayar','lunas'])->default('belum_bayar');
            $table->date('tanggal_bayar')->nullable();
            $table->string('file_invoice')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
