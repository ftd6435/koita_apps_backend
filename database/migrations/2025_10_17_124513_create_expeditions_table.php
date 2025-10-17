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
        Schema::create('expeditions', function (Blueprint $table) {
            $table->id();

            // 🔹 Fondation concernée (barre fondue)
            $table->foreignId('id_barre_fondu')
                ->constrained('fondations')
                ->cascadeOnDelete();

            // 🔹 Initialisation de livraison liée
            $table->foreignId('id_init_livraison')
                ->constrained('init_livraisons')
                ->cascadeOnDelete();

            // 🔹 Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('modify_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('expeditions');
    }
};
