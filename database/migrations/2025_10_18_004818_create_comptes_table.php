<?php

use App\Modules\Comptabilite\Models\Banque;
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
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Banque::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Devise::class)->constrained()->cascadeOnDelete();
            $table->decimal('solde_initial', 15, 2)->default(0.00);
            $table->string('numero_compte')->unique();
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
