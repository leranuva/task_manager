<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task_statuses', function (Blueprint $table) {
            // Agregar columna 'is_final' si no existe
            if (!Schema::hasColumn('task_statuses', 'is_final')) {
                $table->boolean('is_final')->default(false)->after('is_default');
            }
        });

        // Renombrar 'order' a 'position' usando DB raw
        if (Schema::hasColumn('task_statuses', 'order') && !Schema::hasColumn('task_statuses', 'position')) {
            DB::statement('ALTER TABLE task_statuses CHANGE `order` `position` INTEGER NOT NULL DEFAULT 0');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_statuses', function (Blueprint $table) {
            if (Schema::hasColumn('task_statuses', 'is_final')) {
                $table->dropColumn('is_final');
            }
        });

        if (Schema::hasColumn('task_statuses', 'position') && !Schema::hasColumn('task_statuses', 'order')) {
            DB::statement('ALTER TABLE task_statuses CHANGE `position` `order` INTEGER NOT NULL DEFAULT 0');
        }
    }
};
