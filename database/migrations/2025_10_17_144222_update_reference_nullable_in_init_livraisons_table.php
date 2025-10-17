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
        Schema::table('init_livraisons', function (Blueprint $table) {
            // ✅ Rendre la colonne `reference` nullable
            $table->string('reference', 100)->nullable()->change();
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::table('init_livraisons', function (Blueprint $table) {
            // 🔁 Revenir à NOT NULL (si elle ne devait pas l’être)
            $table->string('reference', 100)->nullable(false)->change();
        });
    }
};
