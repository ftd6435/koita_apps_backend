<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Comptabilite\Models\CompteDevise;
use App\Modules\Comptabilite\Models\FournisseurOperation;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devise extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'devises';

    protected $fillable = [
        'created_by',
        'modify_by',
    ];

    // ==============================
    // 🔹 RELATIONS
    // ==============================

    /**
     * Utilisateur ayant créé la devise.
     */
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Utilisateur ayant modifié la devise.
     */
    public function modificateur()
    {
        return $this->belongsTo(User::class, 'modify_by');
    }

    // ==============================
    // 🔹 ACCESSORS MODERNES (Laravel 12)
    // ==============================

    /**
     * 🔹 Libellé complet de la devise (ex : "Franc Guinéen (FG)").
     */
    protected function libelleComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->libelle.($this->symbole ? " ({$this->symbole})" : ''))
        );
    }

    public function fournisseurOperation(): HasMany
    {
        return $this->hasMany(FournisseurOperation::class)->whereNull('fournisseur_operations.deleted_at');
    }

    public function deviseComptes(): HasMany
    {
        return $this->hasMany(CompteDevise::class);
    }
}
