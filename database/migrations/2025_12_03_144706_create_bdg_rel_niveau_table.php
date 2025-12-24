<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_rel_niveau', function (Blueprint $table) {

            $table->id('IDbdg_rel_niveau');
            $table->integer('idbdg_niveau1')->default(0);
            $table->integer('idbdg_niveau2')->default(0);

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);

            $table->tinyInteger('dep_recette')->default(0);
            $table->integer('niveau')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);

            $table->bigInteger('IDBdg_Compte')->default(0);

            // Indexes
            $table->index(['niveau', 'idbdg_niveau1', 'idbdg_niveau2'], 'WDIDX_bdg_rel_niveau_idbdg_Cle_comp');
            $table->index('IDBdg_Compte', 'WDIDX_bdg_rel_niveau_IDBdg_Compte');
            $table->index('idbdg_niveau1', 'WDIDX_bdg_rel_niveau_idbdg_niveau1');
            $table->index('idbdg_niveau2', 'WDIDX_bdg_rel_niveau_idbdg_niveau2');
            $table->index('IDLogin', 'WDIDX_bdg_rel_niveau_IDLogin');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_rel_niveau');
    }
};
