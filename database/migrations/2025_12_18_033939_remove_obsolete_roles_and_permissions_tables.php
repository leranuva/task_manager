<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Elimina las tablas obsoletas de roles y permissions
     * ya que ahora los roles son strings en pivots y los permisos son lógicos
     */
    public function up(): void
    {
        // Eliminar tablas pivote primero (dependencias)
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        
        // Eliminar tablas principales
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     * 
     * ⚠️ No se puede revertir completamente sin datos
     */
    public function down(): void
    {
        // No revertir - estas tablas ya no se usan
        // Si necesitas recrearlas, usa las migraciones originales
    }
};
