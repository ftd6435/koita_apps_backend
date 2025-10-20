<?php

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Comptabilite\Models\Compte;
use App\Modules\Comptabilite\Models\TypeOperation;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fournisseur_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Fournisseur::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(TypeOperation::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Compte::class)->constrained()->cascadeOnDelete();
            $table->decimal('taux', 8, 2)->default(0.00);
            $table->decimal('montant', 15, 2)->default(0.00);
            $table->text('commentaire')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fournisseur_operations');
    }
};
