<?php

use App\Modules\Comptabilite\Models\Compte;
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
        Schema::create('compte_devises', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Compte::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Devise::class)->constrained()->cascadeOnDelete();
            $table->decimal('solde_initial', 15, 2)->default(0.00);
            $table->decimal('solde_courant', 15, 2)->default(0.00);
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compte_devises');
    }
};
