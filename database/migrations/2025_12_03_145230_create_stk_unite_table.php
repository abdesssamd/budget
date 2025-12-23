<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_unite', function (Blueprint $table) {

            $table->increments('IDUnite');

            $table->string('Libellé', 50)->nullable();
            $table->string('ABV', 5)->nullable();
            $table->string('Type', 1)->nullable();

            $table->integer('unitecoff')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_unite');
    }
};
