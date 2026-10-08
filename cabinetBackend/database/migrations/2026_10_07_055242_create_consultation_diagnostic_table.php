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
        Schema::create('consultation_diagnostic', function (Blueprint $table) {
            $table->integer('idConsultation');
            $table->foreign('idConsultation')->references('idConsultation')->on('consultations')->onDelete('cascade');
            $table->string('codeDiagnostic');
            $table->foreign('codeDiagnostic')->references('codeDiagnostic')->on('diagnostics')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_diagnostic');
    }
};
