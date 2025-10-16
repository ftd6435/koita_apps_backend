<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécution de la migration.
     */
    public function up(): void
    {
        Schema::create('devises', function (Blueprint $table) {
            $table->id();

            // 🔹 Champs principaux
            $table->string('libelle', 100);             // ex : Franc Guinéen
            $table->string('symbole', 10)->nullable();  // ex : FG, $, €
            $table->decimal('taux_change', 15, 6)->nullable(); // taux très précis

            // 🔹 Champs d’audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 🔹 Timestamps + SoftDeletes
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('devises');
    }
};
