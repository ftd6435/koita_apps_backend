<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rendre la colonne `prix_unitaire` nullable dans fixing_clients
     */
    public function up(): void
    {
        Schema::table('fixing_clients', function (Blueprint $table) {
            $table->decimal('prix_unitaire', 10, 5)
                ->default(0)
                ->change();
        });
    }

    /**
     * Revenir à l’état précédent (non nullable)
     */
    public function down(): void
    {
        Schema::table('fixing_clients', function (Blueprint $table) {
            $table->decimal('prix_unitaire', 10, 5)
                ->default(0.00)
                ->change();
        });
    }
};
