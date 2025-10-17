<?php

use App\Modules\Fixing\Models\Fixing;
use App\Modules\Purchase\Models\Barre;
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
        Schema::table('fixing_barre', function (Blueprint $table) {
            $table->foreignIdFor(Fixing::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Barre::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->cascadeOnDelete();

            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fixing_barre', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Fixing::class);
            $table->dropConstrainedForeignIdFor(Barre::class);
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropSoftDeletes();
        });
    }
};
