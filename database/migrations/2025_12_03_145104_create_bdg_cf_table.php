<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_cf', function (Blueprint $table) {

            $table->bigInteger('IDbdg_CF')->primary();

            $table->date('Date_cf')->nullable();
            $table->date('Date_envoi')->nullable();
            $table->string('VISA_cf', 50)->nullable();
            $table->date('Date_retour')->nullable();

            $table->dateTime('Creer_le')->nullable();

            $table->bigInteger('IDLogin')->nullable();

            $table->longBlob('Photo')->nullable();

            $table->bigInteger('IDOperation_Budg')->nullable();

            $table->longText('Observations')->nullable();

            // Indexes
            $table->index('IDLogin', 'WDIDX15338205600');
            $table->index('IDOperation_Budg', 'WDIDX15338205601');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_cf');
    }
};
