<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('bdg_facture', function (Blueprint $table) {
        // Ajoute la colonne Montant (15 chiffres, 2 après la virgule)
        $table->decimal('Montant', 15, 2)->default(0)->after('date_facture'); 
    });
}

public function down()
{
    Schema::table('bdg_facture', function (Blueprint $table) {
        $table->dropColumn('Montant');
    });
}
};
