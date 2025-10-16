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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();

            // 🔹 Champs principaux
            $table->string('nom');                // non nullable
            $table->string('prenom');             // non nullable
            $table->string('telephone')->unique()->nullable();
            $table->string('adresse')->nullable();
            $table->string('email')->unique()->nullable();

            // 🔹 Champs d’audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            // 🔹 Timestamps
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
