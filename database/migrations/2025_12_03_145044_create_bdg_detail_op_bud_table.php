<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bdg_detail_op_bud', function (Blueprint $table) {

            $table->bigInteger('IDDetail_op_bud')->primary();

            $table->decimal('Montant', 24, 6)->default(0.000000);
            $table->string('designation', 50)->nullable();
            $table->longText('Observations')->nullable();

            $table->timestamp('Creer_le')->useCurrent()->useCurrentOnUpdate();

            $table->bigInteger('IDLogin')->default(0);
            $table->bigInteger('IDOperation_Budg')->nullable();
            $table->bigInteger('IDMandat')->nullable();
            $table->bigInteger('NumFournisseur')->nullable();

            $table->decimal('mantant_mandat', 24, 6)->default(0.000000);

            // Indexes
            $table->index('IDLogin', 'WDIDX_bdg_Detail_op_bud_IDLogin');
            $table->index('IDMandat', 'WDIDX_bdg_Detail_op_bud_IDMandat');
            $table->index('IDOperation_Budg', 'WDIDX_bdg_Detail_op_bud_IDOperation_Budg');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_detail_op_bud');
    }
};
