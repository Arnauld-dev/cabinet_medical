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
        Schema::create('analyse_laboratoire', function (Blueprint $table) {
            $table->integer('idAnalyse')->primary();
            $table->integer('idPatient');
            $table->foreign('idPatient')->references('idPatient')->on('patients')->onDelete('cascade');
            $table->integer('idConsultation');
            $table->foreign('idConsultation')->references('idConsultation')->on('consultations')->onDelete('cascade');
            $table->string('typeAnalyse');
            $table->date('datePrescription');
            $table->string('laboratoire');
            $table->string('resultat')->nullable();
            $table->string('dateResultat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analyse_laboratoire');
    }
};
