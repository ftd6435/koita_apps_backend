<?php

namespace App\Modules\Administration\Models;

use App\Modules\Comptabilite\Models\FournisseurOperation;
use App\Modules\Purchase\Models\Achat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'adresse',
        'telephone',
        'image',
        'created_by',
        'updated_by',
    ];

    public function achats() : HasMany
    {
        return $this->hasMany(Achat::class)->whereNull('achats.deleted_at');
    }

    public function operations() : HasMany
    {
        return $this->hasMany(FournisseurOperation::class)->whereNull('fournisseur_operations.deleted_at');
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
