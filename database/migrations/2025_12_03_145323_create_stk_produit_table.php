<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_produit', function (Blueprint $table) {

            $table->string('LibProd', 40);
            $table->longBlob('Photo')->nullable();

            $table->integer('QteReappro')->default(0);
            $table->integer('QteMini')->default(0);

            $table->string('Reference', 20);

            $table->longText('Description')->nullable();
            $table->tinyInteger('PlusAuCatalogue')->default(0);
            $table->string('GenCode', 40)->nullable();
            $table->string('CodeBarre', 40)->nullable();

            $table->string('SaisiPar', 40)->nullable();

            $table->timestamp('SaisiLe')->useCurrent()->useCurrentOnUpdate();

            $table->string('CodeFamille', 40)->nullable();
            $table->string('CodePort', 20)->nullable();

            $table->tinyInteger('Ver_perime')->default(0);
            $table->tinyInteger('Ver_condition')->default(0);

            $table->integer('unite')->default(1);
            $table->string('Stock_Sec', 50)->default('0');

            $table->increments('id_produit');

            $table->integer('IDUnite')->default(1);

            $table->tinyInteger('Ver_immo')->default(0);
            $table->integer('IDFamille_Prod')->default(0);

            $table->tinyInteger('Ver_tva')->default(0);
            $table->double('TauxTVA')->default(0);

            $table->integer('IDmagasin')->default(0);
            $table->tinyInteger('Archive')->default(0);
            $table->tinyInteger('ver_balance')->default(0);

            // Indexes
            $table->index('Reference', 'WDIDXReference');
            $table->index('LibProd', 'WDIDX14829653343');
            $table->index('QteReappro', 'WDIDX14829653354');
            $table->index('QteMini', 'WDIDX14829653355');
            $table->index('GenCode', 'WDIDX14829653356');
            $table->index('CodeBarre', 'WDIDX14829653357');
            $table->index('CodePort', 'WDIDX148296533610');
            $table->index('SaisiPar', 'WDIDX14829653368');
            $table->index('CodeFamille', 'WDIDX14829653369');
            $table->index('IDUnite', 'WDIDX148296533711');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_produit');
    }
};
