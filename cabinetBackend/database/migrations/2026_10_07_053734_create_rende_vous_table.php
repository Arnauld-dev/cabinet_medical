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
        Schema::create('rendeVous', function (Blueprint $table) {
            $table->id('idRendeVous');
            $table->integer('idPatient');
            $table->foreign('idPatient')->references('idPatient')->on('patients')->onDelete('cascade');
            $table->integer('idMedecin');
            $table->foreign('idMedecin')->references('idMedecin')->on('medecins')->onDelete('cascade');
            $table->dateTime('dateHeure');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rende_vous');
    }
};
