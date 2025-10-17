<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécution de la migration : modification de la table clients.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            // 🔹 Suppression des anciennes colonnes si elles existent
            if (Schema::hasColumn('clients', 'nom')) {
                $table->dropColumn('nom');
            }

            if (Schema::hasColumn('clients', 'prenom')) {
                $table->dropColumn('prenom');
            }

            // 🔹 Ajout du nom complet
            $table->string('nom_complet')->after('id');

            // 🔹 Ajout de la raison sociale (nullable car pas tous les clients sont des entreprises)
            $table->string('raison_sociale')->nullable()->after('nom_complet');

            // 🔹 Ajout des informations de localisation
            $table->string('pays')->nullable();
            $table->string('ville')->nullable();
            $table->string('adresse')->nullable()->change(); // s’assure qu’elle reste nullable
        });
    }

    /**
     * Annulation des modifications.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {

            // 🔹 Suppression des nouvelles colonnes
            if (Schema::hasColumn('clients', 'nom_complet')) {
                $table->dropColumn('nom_complet');
            }

            if (Schema::hasColumn('clients', 'raison_sociale')) {
                $table->dropColumn('raison_sociale');
            }

            if (Schema::hasColumn('clients', 'pays')) {
                $table->dropColumn('pays');
            }

            if (Schema::hasColumn('clients', 'ville')) {
                $table->dropColumn('ville');
            }

            // 🔹 Restauration des anciennes colonnes
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();
        });
    }
};
