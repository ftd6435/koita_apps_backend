<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'devise_id',
        'libelle',
        'numero_compte',
        'solde_initial',
        'commentaire',
        'created_by',
        'updated_by'
    ];

    public function fournisseurOperations() : HasMany
    {
        return $this->hasMany(FournisseurOperation::class)->whereNull('fournisseur_operations.deleted_at');
    }

    public function devise() : BelongsTo
    {
        return $this->belongsTo(Devise::class)->whereNull('devises.deleted_at');
    }

    public function createdBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy() : BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
