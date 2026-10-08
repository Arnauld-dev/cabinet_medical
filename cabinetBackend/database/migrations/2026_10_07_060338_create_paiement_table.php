<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paiement', function (Blueprint $table) {
            $table->id('idPaiement');
            $table->unsignedBigInteger('idFacture');
            $table->foreign('idFacture')->references('idFacture')->on('facture')->onDelete('cascade');
            $table->date('datePaiement');
            $table->decimal('montant', 10, 2);
            $table->string('modePaiement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paiement');
    }
};
