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
        Schema::create('recintoselectorales', function (Blueprint $table) {
            $table->id();
            $table->text('recinto');                 
            $table->boolean('estado')->default(true);
            $table->foreignId('parroquia_id')->constrained('parroquias');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recintoselectorales');
    }
};
