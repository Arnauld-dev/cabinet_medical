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
        Schema::create('facture', function (Blueprint $table) {
            $table->id('idFacture');
            $table->string('numeroFacture')->unique();
            $table->integer('idPatient');
            $table->foreign('idPatient')->references('idPatient')->on('patients')->onDelete('cascade');
            $table->integer('idConsultation');
            $table->foreign('idConsultation')->references('idConsultation')->on('consultations')->onDelete('cascade');
            $table->date('dateFacturation');
            $table->decimal('montant', 10, 2);
            $table->string('statut')->default('en attente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facture');
    }
};
