<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();

            // 🔹 Liens externes
            $table->foreignId('id_devise')
                ->constrained('devises')
                ->cascadeOnDelete();

            $table->foreignId('id_type_operation')
                ->constrained('type_operations')
                ->cascadeOnDelete();

            // 🔹 Champs principaux
            $table->string('reference', 100)->nullable();
            $table->date('date_operation')->nullable();
            $table->decimal('montant', 15, 2);
            $table->string('commentaire', 255)->nullable();

            // 🔹 Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
