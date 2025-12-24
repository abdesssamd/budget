<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stk_fournisseur', function (Blueprint $table) {

            $table->bigIncrements('NumFournisseur');

            $table->string('Nom', 40)->nullable();
            $table->string('Societe', 40)->nullable();
            $table->string('Adresse', 150)->nullable();
            $table->string('Telephone', 20)->nullable();
            $table->string('Fax', 20)->nullable();
            $table->string('EMail', 40)->nullable();
            $table->string('Pays', 40)->nullable();
            $table->string('Mobile', 20)->nullable();
            $table->longText('Observations')->nullable();

            $table->string('CodePostal', 5)->nullable();
            $table->string('Ville', 40)->nullable();
            $table->string('Civilite', 5)->nullable();
            $table->string('Prénom', 50)->nullable();

            $table->string('num_carte_fiscale', 20)->nullable();
            $table->string('num_registre_commerce', 20)->nullable();
            $table->string('NIS', 50)->nullable();

            // Indexes
            $table->index(['Societe', 'CodePostal'], 'OptimCleComp_1');
            $table->index('Nom', 'WDIDX148296533712');
            $table->index('Societe', 'WDIDX148296533713');
            $table->index('Mobile', 'WDIDX148296533714');
            $table->index('CodePostal', 'WDIDX148296533815');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stk_fournisseur');
    }
};
