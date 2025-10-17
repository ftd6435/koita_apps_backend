<?php

namespace App\Modules\Fixing\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Purchase\Models\Barre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixingBarre extends Model
{
    use SoftDeletes;

    protected $table = "fixing_barre";

    protected $fillable = [
        'fixing_id',
        'barre_id',
        'created_by',
        'updated_by',
    ];

    public function fixing() : BelongsTo
    {
        return $this->belongsTo(Fixing::class)->whereNull('fixings.deleted_at');
    }

    public function barre() : BelongsTo
    {
        return $this->belongsTo(Barre::class)->whereNull('barres.deleted_at');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
