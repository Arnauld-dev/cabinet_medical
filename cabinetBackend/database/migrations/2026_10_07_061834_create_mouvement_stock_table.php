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
        Schema::create('mouvement_stock', function (Blueprint $table) {
            $table->integer('idMouvement')->primary();
            $table->integer('idStock');
            $table->foreign('idStock')->references('idStock')->on('stock_medicament')->onDelete('cascade');
            $table->string('typeMouvement');
            $table->integer('quantite');
            $table->date('dateMouvement');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvement_stock');
    }
};
