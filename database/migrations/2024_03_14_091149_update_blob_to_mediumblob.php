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
        // Agregar la nueva columna content como mediumblob
        Schema::table('files', function (Blueprint $table) {
            $table->binary('content')->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la nueva columna content si es necesario
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
