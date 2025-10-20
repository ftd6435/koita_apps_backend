<?php

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
        Schema::create('comptes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Devise::class)->constrained()->cascadeOnDelete();
            $table->string('libelle', length: 100)->unique();
            $table->string('numero_compte', length: 100)->unique();
            $table->decimal('solde_initial', 15, 2)->default(0.00);
            $table->text("commentaire")->nullable();
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
        Schema::dropIfExists('comptes');
    }
};
