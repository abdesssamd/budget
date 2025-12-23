<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_free_mission', function (Blueprint $table) {

            // CORRECTION 1 : bigIncrements pour gérer l'AUTO_INCREMENT + Primary Key
            $table->bigIncrements('IDbdg_Free_mission');

            $table->string('Reference', 20)->nullable()->unique();
            $table->date('Date_mission')->nullable();

            $table->integer('Num')->default(0);
            $table->string('b_Objet', 50)->nullable();

            $table->integer('IDPersonel')->default(0);
            $table->bigInteger('IDparam_wilaya')->default(0);

            $table->string('Etablissemen_des', 100)->nullable();
            $table->string('Itineraire', 100)->nullable();

            $table->timestamp('date_depart')->useCurrent()->useCurrentOnUpdate();
            
            // CORRECTION 2 : Suppression du default '0000-00-00' qui fait planter MySQL
            $table->timestamp('date_arrive')->nullable();

            $table->decimal('frais_sup', 24, 6)->nullable();

            $table->bigInteger('IDparam_Fo_Moyen_tronsport')->default(0);

            $table->bigInteger('IDSection')->default(0);
            $table->bigInteger('IDObj1')->default(0);
            $table->integer('IDObj2')->default(0);
            $table->bigInteger('IDObj3')->default(0);
            $table->bigInteger('IDObj4')->default(0);
            $table->integer('IDObj5')->default(0);

            $table->bigInteger('IDBudjet')->default(0);

            $table->decimal('MontantFM', 24, 6)->default(0.000000);
            $table->decimal('MontantFM_total', 24, 6)->default(0.000000);

            // CORRECTION 3 : Idem, on supprime le default zéro date
            $table->timestamp('Creer_le')->nullable();

            $table->bigInteger('IDLogin')->default(0);
            $table->integer('IDExercice')->default(0);

            // Indexes
            $table->index('Reference');
            $table->index('Date_mission', 'WDIDX_bdg_Free_mission_Date_mission');
            $table->index('IDBudjet', 'WDIDX_bdg_Free_mission_IDBudjet');
            
            // Index composite
            $table->index(
                ['IDSection','IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','IDBudjet'],
                'WDIDX_bdg_Free_mission_IDClecompose'
            );
            
            $table->index('IDObj1', 'WDIDX_bdg_Free_mission_IDObj1');
            $table->index('IDObj2', 'WDIDX_bdg_Free_mission_IDObj2');
            $table->index('IDObj3', 'WDIDX_bdg_Free_mission_IDObj3');
            $table->index('IDObj4', 'WDIDX_bdg_Free_mission_IDObj4');
            $table->index('IDObj5', 'WDIDX_bdg_Free_mission_IDObj5');
            $table->index('IDparam_Fo_Moyen_tronsport', 'WDIDX_bdg_Free_mission_IDparam_Fo_Moyen_tronsport');
            $table->index('IDparam_wilaya', 'WDIDX_bdg_Free_mission_IDparam_wilaya');
            $table->index('IDPersonel', 'WDIDX_bdg_Free_mission_IDPersonel');
            $table->index('IDSection', 'WDIDX_bdg_Free_mission_IDSection');
            $table->index('Num', 'WDIDX_bdg_Free_mission_Num');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_free_mission');
    }
};