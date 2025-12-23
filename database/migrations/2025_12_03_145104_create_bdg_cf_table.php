<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Important pour exécuter la commande SQL brute

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_cf', function (Blueprint $table) {
            
            // Correction 1 : bigIncrements gère la Clé Primaire + Auto Increment
            $table->bigIncrements('IDbdg_CF');

            $table->date('Date_cf')->nullable();
            $table->date('Date_envoi')->nullable();
            $table->string('VISA_cf', 50)->nullable();
            $table->date('Date_retour')->nullable();

            $table->dateTime('Creer_le')->nullable();

            $table->bigInteger('IDLogin')->nullable();

            // Correction 2 : On crée d'abord en BLOB standard (binary)
            // On le transformera en LONGBLOB juste après
            $table->binary('Photo')->nullable();

            $table->bigInteger('IDOperation_Budg')->nullable();

            $table->longText('Observations')->nullable();
            
            // Correction 3 : Ajout de la colonne manquante vue dans votre SQL
            $table->string('scan_path', 255)->nullable();

            // Indexes
            $table->index('IDLogin', 'WDIDX15338205600');
            $table->index('IDOperation_Budg', 'WDIDX15338205601');
        });

        // Correction 2 (Suite) : Forcer le type LONGBLOB via SQL direct
        DB::statement("ALTER TABLE bdg_cf MODIFY Photo LONGBLOB");
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_cf');
    }
};