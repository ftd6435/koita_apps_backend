<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajouter la colonne reference à la table fixing_clients.
     */
    public function up(): void
    {
        Schema::table('fixing_clients', function (Blueprint $table) {
            if (!Schema::hasColumn('fixing_clients', 'reference')) {
                $table->string('reference')
                    ->unique()
                    ->nullable()
                    ->after('id');
            }
        });
    }

    /**
     * Supprimer la colonne reference si la migration est annulée.
     */
    public function down(): void
    {
        Schema::table('fixing_clients', function (Blueprint $table) {
            if (Schema::hasColumn('fixing_clients', 'reference')) {
                $table->dropColumn('reference');
            }
        });
    }
};
