<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_operation_budg', function (Blueprint $table) {

            $table->bigInteger('IDOperation_Budg')->primary();

            $table->decimal('Montant_anc', 24, 6)->default(0.000000);

            $table->integer('Num_operation')->default(0);

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);

            $table->bigInteger('IDObj1')->default(0);
            $table->bigInteger('IDObj2')->nullable();
            $table->bigInteger('IDObj3')->default(0);
            $table->bigInteger('IDObj4')->default(0);
            $table->bigInteger('IDObj5')->default(0);

            $table->integer('Type_operation')->default(0);
            $table->integer('type_incorp')->default(0);
            $table->tinyInteger('decouvert')->default(0);

            $table->bigInteger('IDbdg_rel_niveau')->default(0);

            $table->string('designation', 50)->nullable();

            $table->smallInteger('EXERCICE')->default(0);
            $table->bigInteger('IDBudjet')->default(0);
            $table->bigInteger('IDSection')->default(0);

            // Indexes
            $table->index('EXERCICE', 'WDIDX_bdg_Operation_Budg_EXERCICE');
            $table->index('IDbdg_rel_niveau', 'WDIDX_bdg_Operation_Budg_IDbdg_rel_niveau');
            $table->index('IDBudjet', 'WDIDX_bdg_Operation_Budg_IDBudjet');
            $table->index('IDLogin', 'WDIDX_bdg_Operation_Budg_IDLogin');
            $table->index('IDObj1', 'WDIDX_bdg_Operation_Budg_IDObj1');
            $table->index('IDObj2', 'WDIDX_bdg_Operation_Budg_IDObj2');
            $table->index('IDObj3', 'WDIDX_bdg_Operation_Budg_IDObj3');
            $table->index('IDObj4', 'WDIDX_bdg_Operation_Budg_IDObj4');
            $table->index('IDObj5', 'WDIDX_bdg_Operation_Budg_IDObj5');
            $table->index('IDSection', 'WDIDX_bdg_Operation_Budg_IDSection');
            $table->index(
                ['IDSection','IDObj1','IDObj2','IDObj3','IDObj4','IDObj5','EXERCICE','IDBudjet'],
                'WDIDX_bdg_Operation_Budg_IDSectionIDObj1IDObj2IDObj3IDObj400000'
            );
            $table->index('Num_operation', 'WDIDX_bdg_Operation_Budg_Num_operation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_operation_budg');
    }
};
