<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_operation_rest', function (Blueprint $table) {

            $table->id('IDre_Operation_Budg');
            $table->integer('Num_operation')->default(0);

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('IDObj1')->default(0);
            $table->bigInteger('IDObj2')->default(0);
            $table->bigInteger('IDObj3')->default(0);
            $table->bigInteger('IDObj4')->default(0);
            $table->bigInteger('IDbdg_rel_niveau')->default(0);
            $table->bigInteger('IDObj5')->default(0);

            $table->smallInteger('EXERCICE')->default(0);
            $table->bigInteger('IDBudjet')->default(0);
            $table->bigInteger('IDSection')->default(0);

            $table->decimal('Mt_genr', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_Nv', 24, 6)->default(0.000000);
            $table->decimal('Mt_Total', 24, 6)->default(0.000000);

            $table->integer('niveau')->default(0);

            $table->decimal('Mt_Budget_total', 24, 6)->default(0.000000);
            $table->decimal('Mt_Budget_sup', 24, 6)->default(0.000000);
            $table->decimal('Mt_projet_sup', 24, 6)->default(0.000000);

            $table->string('Num_BS', 50)->nullable();
            $table->string('Num_PS', 50)->nullable();

            // Indexes
            $table->index('EXERCICE', 'WDIDX_bdg_Operation_rest_EXERCICE');
            $table->index('IDbdg_rel_niveau', 'WDIDX_bdg_Operation_rest_IDbdg_rel_niveau');
            $table->index('IDBudjet', 'WDIDX_bdg_Operation_rest_IDBudjet');
            $table->index(
                ['IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','EXERCICE','IDBudjet','IDSection'],
                'WDIDX_bdg_Operation_rest_IDClecompose'
            );
            $table->index('IDLogin', 'WDIDX_bdg_Operation_rest_IDLogin');
            $table->index('IDObj1', 'WDIDX_bdg_Operation_rest_IDObj1');
            $table->index('IDObj2', 'WDIDX_bdg_Operation_rest_IDObj2');
            $table->index('IDObj3', 'WDIDX_bdg_Operation_rest_IDObj3');
            $table->index('IDObj4', 'WDIDX_bdg_Operation_rest_IDObj4');
            $table->index('IDObj5', 'WDIDX_bdg_Operation_rest_IDObj5');
            $table->index('IDSection', 'WDIDX_bdg_Operation_rest_IDSection');
            $table->index('Num_operation', 'WDIDX_bdg_Operation_rest_Num_operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_operation_rest');
    }
};
