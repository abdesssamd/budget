<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_bon_commande', function (Blueprint $table) {

            $table->increments('IDBON');

            $table->string('Type_bon', 1)->nullable();
            $table->date('date')->nullable();
            $table->dateTime('date_enr')->nullable();
            $table->string('designation', 50)->nullable();
            $table->string('Num_bon', 15)->nullable();
            $table->string('num_piece', 15)->nullable();

            $table->string('SaisiPar', 40)->nullable();
            $table->dateTime('SaisiLe')->nullable();

            $table->decimal('prixtotal', 24, 6)->nullable();
            $table->tinyInteger('valider')->nullable();

            $table->bigInteger('NumFournisseur')->nullable();
            $table->string('valid_saisipar', 40)->nullable();
            $table->dateTime('valide_le')->nullable();

            $table->integer('IDExercice')->nullable();
            $table->tinyInteger('Etat_commande')->default(0);

            // Indexes
            $table->index('date', 'WDIDX15099060620');
            $table->index('SaisiPar', 'WDIDX15099060631');
            $table->index('NumFournisseur', 'WDIDX15099060632');
            $table->index('IDExercice', 'WDIDX15099060633');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_bon_commande');
    }
};
