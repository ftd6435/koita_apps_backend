<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Modules\Settings\Models\Client;
use App\Modules\Settings\Models\Devise;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixing_clients', function (Blueprint $table) {
            $table->id();

            // 🔹 Client concerné
            $table->foreignId('id_client')
                ->constrained('clients')
                ->cascadeOnDelete();

            // 🔹 Données principales du fixing
            $table->decimal('poids_pro', 10, 5)->default(0.00);
            $table->decimal('carrat_moyen', 10, 5)->default(0.00);
            $table->decimal('discompte', 10, 5)->default(0.00);
            $table->decimal('bourse', 10, 5)->default(0.00);
            $table->decimal('prix_unitaire', 10, 5)->nullable();

            // 🔹 Devise associée
            $table->foreignId('id_devise')
                ->constrained('devises')
                ->cascadeOnDelete();

            // 🔹 Statut du fixing
            $table->enum('status', ['en attente', 'confirmer', 'valider'])
                ->default('en attente');

            // 🔹 Audit
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixing_clients');
    }
};
