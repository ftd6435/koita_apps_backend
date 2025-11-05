<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banque extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'libelle',
        'api',
        'commentaire',
        'created_by',
        'updated_by'
    ];

    public function fournisseurOperations() : HasMany
    {
        return $this->hasMany(FournisseurOperation::class)->whereNull('fournisseur_operations.deleted_at');
    }

    public function createdBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function compte(): HasMany
    {
        return $this->hasMany(Compte::class);
    }
}
