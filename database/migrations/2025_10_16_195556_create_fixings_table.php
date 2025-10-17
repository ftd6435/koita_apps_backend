<?php

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fixings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Fournisseur::class)->constrained()->cascadeOnDelete();
            $table->decimal('poids_pro', 10, 5)->default(0.00);
            $table->decimal('carrat_moyenne', 10, 5)->default(0.00);
            $table->decimal('discount', 10, 5)->default(0.00);
            $table->decimal('bourse', 10, 5)->default(0.00);
            $table->decimal('unit_price', 10, 5)->default(0.00);
            $table->foreignIdFor(Devise::class)->constrained()->cascadeOnDelete();
            $table->enum('status', ['en attente', 'confirmer', 'valider'])->default('en attente');
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
        Schema::dropIfExists('fixings');
    }
};
