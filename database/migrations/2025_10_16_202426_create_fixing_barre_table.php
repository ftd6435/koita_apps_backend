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
        Schema::create('fixing_barre', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Fixing::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Barre::class)->constrained()->cascadeOnDelete();
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
        Schema::dropIfExists('fixing_barre');
    }
};
