<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_mandat', function (Blueprint $table) {

            $table->id('IDMandat');

            $table->string('Num_mandat', 50)->nullable();
            $table->date('date_mandate')->nullable();

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('NumFournisseur')->default(0);

            $table->date('Date_envoi')->nullable();
            $table->date('Date_retour')->nullable();

            $table->smallInteger('EXERCICE')->default(0);

            $table->bigInteger('IDBudjet')->default(0);
            $table->bigInteger('IDObj1')->default(0);
            $table->integer('IDObj2')->default(0);
            $table->bigInteger('IDObj3')->default(0);
            $table->bigInteger('IDObj4')->default(0);
            $table->integer('IDObj5')->default(0);

            $table->bigInteger('IDSection')->default(0);

            $table->string('designation', 150)->nullable();

            $table->longText('Document_jointe')->nullable();

            // Indexes
            $table->index('EXERCICE', 'WDIDX_bdg_Mandat_EXERCICE');
            $table->index('IDBudjet', 'WDIDX_bdg_Mandat_IDBudjet');
            $table->index(
                ['IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','EXERCICE','IDBudjet','IDSection'],
                'WDIDX_bdg_Mandat_IDClecompose'
            );
            $table->index('IDLogin', 'WDIDX_bdg_Mandat_IDLogin');
            $table->index('IDObj1', 'WDIDX_bdg_Mandat_IDObj1');
            $table->index('IDObj2', 'WDIDX_bdg_Mandat_IDObj2');
            $table->index('IDObj3', 'WDIDX_bdg_Mandat_IDObj3');
            $table->index('IDObj4', 'WDIDX_bdg_Mandat_IDObj4');
            $table->index('IDObj5', 'WDIDX_bdg_Mandat_IDObj5');
            $table->index('IDSection', 'WDIDX_bdg_Mandat_IDSection');
            $table->index('NumFournisseur', 'WDIDX_bdg_Mandat_NumFournisseur');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_mandat');
    }
};
