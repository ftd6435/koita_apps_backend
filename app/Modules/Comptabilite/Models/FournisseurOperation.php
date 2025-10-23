<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Administration\Models\User;
use App\Modules\Settings\Models\Devise;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FournisseurOperation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'date_operation',
        'reference',
        'fournisseur_id',
        'type_operation_id',
        'devise_id',
        'taux',
        'montant',
        'commentaire',
        'created_by',
        'updated_by',
    ];

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class)->whereNull('fournisseurs.deleted_at');
    }

    public function typeOperation(): BelongsTo
    {
        return $this->belongsTo(TypeOperation::class)->whereNull('type_operations.deleted_at');
    }

    public function devise(): BelongsTo
    {
        return $this->belongsTo(Devise::class)->whereNull('devises.deleted_at');
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
