<?php

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
        Schema::table('operations_divers', function (Blueprint $table) {
            if (!Schema::hasColumn('operations_divers', 'reference')) {
                $table->string('reference', 100)->nullable()->after('id_type_operation');
            }

            if (!Schema::hasColumn('operations_divers', 'date_operation')) {
                $table->date('date_operation')->nullable()->after('reference');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operations_divers', function (Blueprint $table) {
            if (Schema::hasColumn('operations_divers', 'reference')) {
                $table->dropColumn('reference');
            }

            if (Schema::hasColumn('operations_divers', 'date_operation')) {
                $table->dropColumn('date_operation');
            }
        });
    }
};
