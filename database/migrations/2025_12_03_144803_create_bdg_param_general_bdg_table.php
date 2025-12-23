<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_param_general_bdg', function (Blueprint $table) {

            $table->bigInteger('IDParam_general_bdg')->primary();

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();
            $table->bigInteger('IDLogin')->default(0);

            $table->integer('nombre_niveau')->default(0);

            $table->string('LIBellé_niveau1_fr', 100)->nullable();
            $table->string('LIBellé_niveau1_ara', 100)->nullable();
            $table->string('LIBellé_niveau2_fr', 100)->nullable();
            $table->string('LIBellé_niveau2_ara', 100)->nullable();
            $table->string('LIBellé_niveau3_fr', 100)->nullable();
            $table->string('LIBellé_niveau3_ara', 100)->nullable();
            $table->string('LIBellé_niveau4_fr', 100)->nullable();
            $table->string('LIBellé_niveau4_ara', 100)->nullable();

            $table->string('Ministaire_tutel', 100)->nullable();
            $table->string('Ministére_de_tutelle_ara', 100)->nullable();

            $table->string('Nom_hie_etatblisement', 150)->nullable();
            $table->string('Nom_hie_etatblisement_ara', 150)->nullable();
            $table->string('Nom_etatblissement', 150)->nullable();
            $table->string('Nom_etatblissement_ara', 150)->nullable();

            $table->string('Nom_hie_etatblisement_second', 150)->nullable();
            $table->string('Nom_hie_etatblisement_second_fr', 150)->nullable();

            $table->string('wilaya', 50)->nullable();
            $table->string('Ville', 40)->nullable();
            $table->string('ville_ara', 50)->nullable();

            $table->string('ordonateur', 50)->nullable();
            $table->string('wilaya_ara', 50)->nullable();

            $table->string('Num_cpt_ordonateur', 50)->nullable();
            $table->string('Num_cpt_tresorier', 50)->nullable();
            $table->string('Num_cpt_ordonateur_ccp', 50)->nullable();

            $table->string('ligne_contable', 50)->nullable();
            $table->string('ligne_contable_ara', 50)->nullable();

            $table->string('EstcequeArticledependpere', 50)->nullable();

            $table->string('LIBellé_niveau5fr', 100)->nullable();
            $table->string('LIBellé_niveau5_ara', 100)->nullable();

            $table->tinyInteger('estcequeArticledependpere_dep')->default(0);
            $table->string('estcequeArborCommun_dep_rec', 50)->nullable();

            $table->string('Daira_ara', 100)->nullable();
            $table->string('Daira_fr', 100)->nullable();
            $table->string('Commune_fr', 100)->nullable();
            $table->string('Commune_ara', 100)->nullable();

            $table->string('Adresse', 150)->nullable();
            $table->string('Adresse_ara', 150)->nullable();

            $table->string('tel', 20)->nullable();
            $table->string('fax', 20)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_param_general_bdg');
    }
};
