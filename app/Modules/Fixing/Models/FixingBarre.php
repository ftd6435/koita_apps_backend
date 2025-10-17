<?php

namespace App\Modules\Fixing\Models;

use App\Modules\Purchase\Models\Barre;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FixingBarre extends Model
{
    use SoftDeletes;

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
}
