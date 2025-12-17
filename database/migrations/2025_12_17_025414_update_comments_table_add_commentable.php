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
        Schema::table('comments', function (Blueprint $table) {
            // Paso 1: Agregar campos commentable primero
            if (!Schema::hasColumn('comments', 'commentable_type')) {
                $table->string('commentable_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('comments', 'commentable_id')) {
                $table->unsignedBigInteger('commentable_id')->nullable()->after('commentable_type');
            }
            
            // Paso 2: Renombrar content a body si existe
            if (Schema::hasColumn('comments', 'content') && !Schema::hasColumn('comments', 'body')) {
                $table->renameColumn('content', 'body');
            }
        });

        // Paso 3: Migrar datos fuera del Schema::table para evitar problemas
        if (Schema::hasColumn('comments', 'task_id')) {
            DB::statement('UPDATE comments SET commentable_type = "App\\\\Models\\\\Task", commentable_id = task_id WHERE task_id IS NOT NULL');
        }

        // Paso 4: Eliminar foreign key primero, luego índice y columna
        Schema::table('comments', function (Blueprint $table) {
            if (Schema::hasColumn('comments', 'task_id')) {
                // Eliminar foreign key primero
                try {
                    $table->dropForeign(['task_id']);
                } catch (\Exception $e) {
                    // La foreign key puede no existir o tener otro nombre
                    // Intentar con el nombre completo
                    try {
                        DB::statement('ALTER TABLE comments DROP FOREIGN KEY comments_task_id_foreign');
                    } catch (\Exception $e2) {
                        // Ignorar si no existe
                    }
                }
                
                // Eliminar índice después de la foreign key
                try {
                    $table->dropIndex('comments_task_id_created_at_index');
                } catch (\Exception $e) {
                    // El índice puede no existir
                }
                
                // Eliminar columna
                $table->dropColumn('task_id');
            }
            
            // Eliminar is_edited si existe
            if (Schema::hasColumn('comments', 'is_edited')) {
                $table->dropColumn('is_edited');
            }
            
            // Agregar índices para commentable
            if (!Schema::hasIndex('comments', 'comments_commentable_type_commentable_id_index')) {
                $table->index(['commentable_type', 'commentable_id'], 'comments_commentable_type_commentable_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // Revertir cambios
            if (Schema::hasColumn('comments', 'body') && !Schema::hasColumn('comments', 'content')) {
                $table->renameColumn('body', 'content');
            }
            
            if (Schema::hasColumn('comments', 'commentable_type')) {
                $table->dropColumn('commentable_type');
            }
            if (Schema::hasColumn('comments', 'commentable_id')) {
                $table->dropColumn('commentable_id');
            }
            
            if (!Schema::hasColumn('comments', 'task_id')) {
                $table->foreignId('task_id')->nullable()->after('body');
                $table->index(['task_id', 'created_at']);
            }
            
            if (!Schema::hasColumn('comments', 'is_edited')) {
                $table->boolean('is_edited')->default(false);
            }
        });
    }
};
