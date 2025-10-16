<?php

namespace App\Modules\Purchase\Models;

use App\Modules\Administration\Models\Fournisseur;
use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Achat extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reference',
        'fournisseur_id',
        'lot_id',
        'commentaire',
        'status',
        'etat',
        'created_by',
        'updated_by'
    ];

    public function fournisseur() : BelongsTo
    {
        return $this->belongsTo(Fournisseur::class)->whereNull('fournisseurs.deleted_at');
    }

    public function lot() : BelongsTo
    {
        return $this->belongsTo(Lot::class)->whereNull('lots.deleted_at');
    }

    public function barres() : HasMany
    {
        return $this->hasMany(Barre::class)->whereNull('barres.deleted_at');
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
