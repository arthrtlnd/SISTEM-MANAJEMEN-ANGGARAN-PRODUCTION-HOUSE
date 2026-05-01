<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop kolom yang bermasalah
        Schema::table('project_crews', function (Blueprint $table) {
            $table->dropColumn('total_gaji');
        });

        // Buat ulang sebagai regular decimal (bukan stored)
        Schema::table('project_crews', function (Blueprint $table) {
            $table->decimal('total_gaji', 15, 2)->default(0)->after('total_hari');
        });
    }

    public function down(): void
    {
        Schema::table('project_crews', function (Blueprint $table) {
            $table->dropColumn('total_gaji');
        });
    }
};
