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
        Schema::create('consultations', function (Blueprint $table) {
            $table->integer('idConsultation')->primary();
            $table->unsignedBigInteger('idRendeVous');
            $table->foreign('idRendeVous')->references('idRendeVous')->on('rendeVous')->onDelete('cascade');
            $table->date('dateConsultation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
