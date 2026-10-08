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
        Schema::create('stock_medicament', function (Blueprint $table) {
            $table->integer('idStock')->primary();
            $table->string('codeMedicament')->unique();
            $table->integer('quantite');
            $table->integer('quantiteMin');
            $table->date('dateExpiration');
            $table->string('fournisseur');
            $table->string('emplacement');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_medicament');
    }
};
