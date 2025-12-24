<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_entree_stock', function (Blueprint $table) {

            $table->string('Reference', 20);
            $table->decimal('PrixAchat', 24, 6);
            $table->double('Quantite')->default(0);
            $table->date('DateAppro');

            $table->bigIncrements('IDEntree');

            $table->longText('Observations');
            $table->string('SaisiPar', 40);

            $table->timestamp('SaisiLe')->useCurrent()->useCurrentOnUpdate();

            $table->tinyInteger('Ver_tva')->default(0);
            $table->double('TauxTVA')->nullable();

            $table->decimal('PrixHT', 24, 6)->default(0.000000);
            $table->double('Qunite_unitaire')->default(0);
            $table->decimal('prixUnitHt', 24, 6)->default(0.000000);

            $table->integer('IDBON')->nullable();
            $table->date('Date_perom')->nullable();

            $table->integer('IDUnite')->nullable();
            $table->tinyInteger('Idunite_type')->default(0);

            $table->decimal('Retenu_Tva', 24, 6)->default(0.000000);
            $table->tinyInteger('MOuv_change')->default(0);

            $table->integer('id_produit')->default(0);
            $table->tinyInteger('neuf')->default(0);
            $table->bigInteger('IDmarque')->default(0);

            $table->decimal('FraisTransport', 24, 6)->default(0.000000);
            $table->decimal('FraisTransportUni', 24, 6)->default(0.000000);

            // Indexes
            $table->index('DateAppro', 'WDIDX_stk_EntréeStock_DateAppro');
            $table->index(['id_produit','IDmarque'], 'WDIDX_stk_EntréeStock_ID_produit_idmarque');
            $table->index(['id_produit','IDUnite'], 'WDIDX_stk_EntréeStock_ID_produit_idunite');
            $table->index('Quantite', 'WDIDX_stk_EntréeStock_Quantite');
            $table->index('Reference', 'WDIDX_stk_EntréeStock_Reference');
            $table->index('SaisiPar', 'WDIDX_stk_EntréeStock_SaisiPar');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_entree_stock');
    }
};
