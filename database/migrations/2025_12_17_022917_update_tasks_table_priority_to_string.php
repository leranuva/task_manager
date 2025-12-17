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
        Schema::table('tasks', function (Blueprint $table) {
            // Cambiar priority de integer a string
            $table->string('priority')->default('normal')->change();
        });

        // Convertir valores existentes
        DB::table('tasks')->where('priority', '0')->update(['priority' => 'low']);
        DB::table('tasks')->where('priority', '1')->update(['priority' => 'normal']);
        DB::table('tasks')->where('priority', '2')->update(['priority' => 'high']);
        DB::table('tasks')->where('priority', '3')->update(['priority' => 'urgent']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convertir valores de vuelta a integer
        DB::table('tasks')->where('priority', 'low')->update(['priority' => '0']);
        DB::table('tasks')->where('priority', 'normal')->update(['priority' => '1']);
        DB::table('tasks')->where('priority', 'high')->update(['priority' => '2']);
        DB::table('tasks')->where('priority', 'urgent')->update(['priority' => '3']);

        Schema::table('tasks', function (Blueprint $table) {
            $table->integer('priority')->default(0)->change();
        });
    }
};
