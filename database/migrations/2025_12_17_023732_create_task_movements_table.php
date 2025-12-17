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
        Schema::create('task_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_status_id')->nullable()->constrained('task_statuses')->onDelete('set null');
            $table->foreignId('to_status_id')->constrained('task_statuses')->onDelete('cascade');
            $table->integer('from_position')->nullable();
            $table->integer('to_position');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['task_id', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_movements');
    }
};
