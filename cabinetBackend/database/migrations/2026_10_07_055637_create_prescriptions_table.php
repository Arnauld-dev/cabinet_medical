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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->integer('idPrescription')->primary();
            $table->integer('idConsultation');
            $table->foreign('idConsultation')->references('idConsultation')->on('consultations')->onDelete('cascade');
            $table->string('codeMedicament');
            $table->foreign('codeMedicament')->references('codeMedicament')->on('medicament')->onDelete('cascade');
            $table->string('posologie');
            $table->integer('dureeTraitement')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
