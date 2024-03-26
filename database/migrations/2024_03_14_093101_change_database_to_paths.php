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
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('content'); // Eliminar la columna 'content'
            $table->string('file_path');   // Agregar la columna 'file_path'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('file_path'); // Eliminar la columna 'file_path'
            $table->binary('content');       // Agregar la columna 'content'
        });
    }
};
