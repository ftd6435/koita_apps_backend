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
        Schema::create('banques', function (Blueprint $table) {
            $table->id();

            // 🔹 Champs principaux
            $table->string('nom_banque'); // non nullable
            $table->string('code_banque', 20)->nullable();
            $table->string('telephone', 20)->nullable()->unique();
            $table->string('adresse')->nullable();
            $table->string('email')->nullable()->unique();

            // 🔹 Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('modify_by')->nullable()->constrained('users')->onDelete('set null');

            // 🔹 Timestamps et soft delete
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Annulation de la migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('banques');
    }
};
