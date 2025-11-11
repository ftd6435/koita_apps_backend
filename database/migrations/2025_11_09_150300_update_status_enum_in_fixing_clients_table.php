<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔹 Convertir toutes les anciennes valeurs invalides vers une valeur par défaut
        DB::statement("UPDATE fixing_clients SET status = 'provisoire' WHERE status NOT IN ('provisoire', 'vendu')");

        // 🔹 Modifier l'énumération
        DB::statement("ALTER TABLE fixing_clients MODIFY status ENUM('provisoire', 'vendu') DEFAULT 'provisoire'");
    }

    public function down(): void
    {
        DB::statement("UPDATE fixing_clients SET status = 'en attente' WHERE status NOT IN ('en attente', 'confirmer', 'valider')");
        DB::statement("ALTER TABLE fixing_clients MODIFY status ENUM('en attente', 'confirmer', 'valider') DEFAULT 'en attente'");
    }
};
