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
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type'); // 'task.created', 'task.updated', 'comment.created', etc.
            $table->string('channel'); // 'in_app', 'email', 'both', 'none'
            $table->boolean('enabled')->default(true);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->timestamps();
            
            $table->unique(['user_id', 'type']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
