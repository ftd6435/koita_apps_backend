<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('operations_divers', function (Blueprint $table) {
            $table->foreignId('id_compte')
                ->nullable()
                ->constrained('comptes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operations_divers', function (Blueprint $table) {
            $table->dropForeign(['id_compte']);
            $table->dropColumn('id_compte');
        });
    }
};
