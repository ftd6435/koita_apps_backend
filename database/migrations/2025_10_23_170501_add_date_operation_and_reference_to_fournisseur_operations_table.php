<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fournisseur_operations', function (Blueprint $table) {
            $table->date('date_operation')->useCurrent();
            $table->string('reference', length: 100)->nullable();
        });

        // Step 2 (later): Add unique index after data is set
        Schema::table('fournisseur_operations', function (Blueprint $table) {
            $table->unique('reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fournisseur_operations', function (Blueprint $table) {
            $table->dropColumn('date_operations');
            $table->dropColumn('reference');
        });
    }
};
