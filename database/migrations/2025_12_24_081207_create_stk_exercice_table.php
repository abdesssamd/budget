<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_exercice', function (Blueprint $table) {
            $table->increments('IDExercice');
            $table->string('Libellé', 50)->nullable();
            $table->string('anne', 4)->nullable();
            $table->tinyInteger('Ouvert')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_exercice');
    }
};
