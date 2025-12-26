<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bdg_pj', function (Blueprint $table) {
            $table->id('ID_PJ');

            $table->unsignedBigInteger('IDOperation_Budg');

            $table->string('chemin_fichier', 255);
            $table->string('nom_fichier', 255);

            $table->timestamp('created_at')->nullable();

            // Foreign key (adjust table name if needed)
            $table->foreign('IDOperation_Budg')
                  ->references('IDOperation_Budg')
                  ->on('bdg_operation_budg')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bdg_pj');
    }
};
