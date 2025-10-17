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
        Schema::table('fondations', function (Blueprint $table) {
            // 🔹 Ajout du champ statut
            $table->enum('statut', ['corriger', 'non corriger'])
                ->default('non corriger')
                ->after('is_fixed'); // positionné après la colonne is_fixed
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::table('fondations', function (Blueprint $table) {
            $table->dropColumn('statut');
        });
    }
};
