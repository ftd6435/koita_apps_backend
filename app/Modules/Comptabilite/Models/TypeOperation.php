<?php

namespace App\Modules\Comptabilite\Models;

use App\Modules\Administration\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TypeOperation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'type_operations';

    protected $fillable = [
        'libelle',
        'nature',
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Utilisateur ayant créé le type d'opération.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié le type d'opération.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSOR MODERNE (Laravel 12)
    // ==============================

    /**
     * 🔹 Libellé formaté de l’opération.
     * Exemple : "Entrée - Achat d’or"
     */
    protected function libelleFormate(): Attribute
    {
        return Attribute::make(
            get: fn () => ucfirst($this->nature) . ' - ' . ucfirst($this->libelle)
        );
    }

    public function fournisseurOperations() : HasMany
    {
        return $this->hasMany(FournisseurOperation::class)->whereNull('fournisseur_operations.deleted_at');
    }
}
