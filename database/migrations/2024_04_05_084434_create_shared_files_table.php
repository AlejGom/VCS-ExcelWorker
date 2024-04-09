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
        Schema::create('shared_files', function (Blueprint $table) {
            $table->id(); // Campo ID
            $table->unsignedBigInteger('shared');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_file');
            $table->timestamps();

            // Definir las claves foráneas
            $table->foreign('id_user')->references('id')->on('users');
            $table->foreign('id_file')->references('id')->on('files');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shared_files');
    }
};
