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
        Schema::create('task_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "To Do", "In Progress", "Done"
            $table->string('slug');
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('color')->default('#6b7280'); // Color del estado
            $table->integer('order')->default(0); // Orden para el Kanban
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->unique(['project_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_statuses');
    }
};
