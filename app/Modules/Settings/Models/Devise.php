<?php

namespace App\Modules\Settings\Models;

use App\Modules\Administration\Models\User;
use App\Modules\Comptabilite\Models\Compte;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Devise extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'devises';

    protected $fillable = [
        'libelle',
        'symbole',
        'taux_change',
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
     * 🔹 Libellé complet de la devise (ex : "Franc Guinéen (FG)")
     */
    protected function libelleComplet(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->libelle . ($this->symbole ? " ({$this->symbole})" : ''))
        );
    }

    public function comptes() : HasMany
    {
        return $this->hasMany(Compte::class)->whereNull('comptes.deleted_at');
    }
}
