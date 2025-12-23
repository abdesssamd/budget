<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_facture', function (Blueprint $table) {

            $table->bigInteger('IDbdg_facture')->primary();

            $table->string('Reference', 20)->unique();
            $table->string('num_facture', 50)->nullable();
            $table->longText('Observations')->nullable();
            $table->date('date_facture')->nullable();

            $table->bigInteger('NumFournisseur')->default(0);
            $table->bigInteger('IDObj1')->default(0);
            $table->integer('IDObj2')->default(0);
            $table->bigInteger('IDObj3')->default(0);
            $table->bigInteger('IDObj4')->default(0);
            $table->integer('IDObj5')->default(0);

            $table->bigInteger('IDSection')->default(0);
            $table->bigInteger('IDBudjet')->default(0);
            $table->integer('IDBON')->default(0);

            $table->integer('Type')->default(0);
            $table->bigInteger('IDOperation_Budg')->default(0);
            $table->integer('IDExercice')->default(0);

            $table->bigInteger('IDMandat')->nullable();
            $table->integer('id_detail_operation')->default(0);

            // Indexes
            $table->index('IDBON', 'WDIDX_bdg_facture_IDBON');
            $table->index('IDBudjet', 'WDIDX_bdg_facture_IDBudjet');
            $table->index(
                ['IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','IDSection','IDBudjet'],
                'WDIDX_bdg_facture_IDClecompose'
            );
            $table->index('IDObj1', 'WDIDX_bdg_facture_IDObj1');
            $table->index('IDObj2', 'WDIDX_bdg_facture_IDObj2');
            $table->index('IDObj3', 'WDIDX_bdg_facture_IDObj3');
            $table->index('IDObj4', 'WDIDX_bdg_facture_IDObj4');
            $table->index('IDObj5', 'WDIDX_bdg_facture_IDObj5');
            $table->index('IDOperation_Budg', 'WDIDX_bdg_facture_IDOperation_Budg');
            $table->index('IDSection', 'WDIDX_bdg_facture_IDSection');
            $table->index('NumFournisseur', 'WDIDX_bdg_facture_NumFournisseur');
            $table->index('Type', 'WDIDX_bdg_facture_Type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_facture');
    }
};
